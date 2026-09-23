# ABVHPS — AWS EC2 production setup (step-by-step)

Prepared 22 Sep 2026. Follow this once, in order. Anything marked **[YOU]** is a step
only the account owner can do (console clicks, DNS). Anything marked **[ME]** is done
over SSH once the server exists — paste the output back if something looks wrong.

## 0. Region — use Mumbai, not Sydney

The project's S3 bucket (`abvhps-media-943088190491-ap-south-1-an`) and `.env` are
already configured for **ap-south-1 (Mumbai)**. Create everything in that region —
it's also the lowest-latency region for Indian visitors and for Razorpay/Cashfree.

**[YOU]** Top-right corner of the AWS Console → region dropdown → **Asia Pacific (Mumbai) ap-south-1**.

## 1. Import the SSH key (so Claude can manage the server directly)

**[YOU]** EC2 → *Key Pairs* → **Import key pair**.
- Name: `abvhps-server`
- Public key value — paste exactly this one line:
  ```
  ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIMA88j7OJZtq6C0vbdbU2Pd62wTmCfi5h6T0IhxTe24B syndigihost-vps
  ```
- Save. (This is a *public* key — safe to store in AWS. The matching private key
  never leaves this machine.)

## 2. Launch the instance

**[YOU]** EC2 → *Instances* → **Launch instances**.
- **Name:** `abvhps-prod`
- **AMI:** Ubuntu Server 22.04 LTS (64-bit x86)
- **Instance type:** `t3.small` (2 GB RAM — needed to run Nginx + PHP-FPM + MySQL +
  the Reverb websocket server + a queue worker on one box comfortably). `t3.micro`
  (free-tier) will be tight once real traffic and Reverb are both running.
- **Key pair:** select `abvhps-server` (imported above)
- **Network settings → Edit** — security group rules:
  | Type | Port | Source |
  |---|---|---|
  | SSH | 22 | My IP (safest) — or Anywhere if your home IP changes often |
  | HTTP | 80 | Anywhere (0.0.0.0/0) |
  | HTTPS | 443 | Anywhere (0.0.0.0/0) |

  Do **not** open port 8080 (Reverb) to the internet — it will be reached only
  through Nginx on port 443, which is safer and avoids a second SSL cert.
- **Storage:** 30 GB gp3 (default 8 GB is too small once uploads and logs grow)
- **Launch instance**

## 3. Give it a fixed IP

A plain EC2 public IP changes if the instance ever restarts. Use an Elastic IP so
DNS never has to change again.

**[YOU]** EC2 → *Elastic IPs* → **Allocate Elastic IP address** → Allocate.
Then select it → **Actions → Associate Elastic IP address** → pick the `abvhps-prod`
instance → Associate.

**[YOU] → tell Claude the Elastic IP address (the four numbers, e.g. `13.234.x.x`).**
Everything from here on is done by Claude over SSH using that IP.

## 4. Point the domain at it (can be done in parallel with step 5)

**[YOU]** In your domain's DNS panel (GoDaddy/Hostinger/wherever `abvhps.org` is
registered):
| Type | Host | Value |
|---|---|---|
| A | `@` | the Elastic IP |
| A | `www` | the Elastic IP |

DNS can take anywhere from a few minutes to a few hours to propagate. The SSL
certificate step (§8) needs this to have propagated, but everything before that
does not.

## 5. Server base setup **[ME, over SSH]**

- `apt update && apt upgrade`, timezone `Asia/Kolkata`, UFW firewall (22/80/443 only)
- PHP 8.2 + required extensions (mbstring, xml, curl, mysql, gd, zip, bcmath, intl)
- Composer, Node.js 20 LTS, Nginx, MySQL 8, Supervisor, Certbot

## 6. Database

- Create MySQL database `abvhps_production` and a dedicated user (not root) with a
  freshly generated password — never reused from the local `.env`.

## 7. Deploy key + clone

- Generate a fresh SSH key **on the server** and add it as a **read-only Deploy Key**
  on the GitHub repo (Settings → Deploy keys) — the server never gets a personal
  GitHub token. **[YOU]** will need to click "Add deploy key" once Claude gives you
  the public key to paste.
- Clone to `/var/www/abvhps` (the path `deploy-aws.sh` already assumes), checkout
  `main` (after the feature branch is merged — see §11).

## 8. Production `.env`

Built from the current local `.env`, with these changes:
- `APP_ENV=production`, `APP_DEBUG=false`
- `APP_URL=https://abvhps.org`
- Fresh `APP_KEY` (`php artisan key:generate`, never reuse the local one)
- `DB_HOST=127.0.0.1` with the new database credentials from §6
- `SESSION_DOMAIN=.abvhps.org`
- `REVERB_HOST=abvhps.org`, `REVERB_SCHEME=https` (Reverb reached via Nginx, not raw 8080)
- The existing S3, Cashfree, Razorpay and Fast2SMS keys carried over as-is —
  **see the payment-mode warning below before accepting real money.**

## 9. Nginx

- Server block for `abvhps.org` serving `public/`, PHP-FPM upstream
- A `location` that reverse-proxies Reverb's websocket upgrade (`/app/...`) to
  `127.0.0.1:8080`, so the browser/app connects to `wss://abvhps.org/app` — no
  separate port or certificate needed for realtime updates

## 10. Supervisor (keeps things running after a reboot or a crash)

- `abvhps-queue`: `php artisan queue:work --sleep=3 --tries=3`
- `abvhps-reverb`: `php artisan reverb:start`

## 11. Go live

- `composer install --no-dev`, `npm ci && npm run build`, `php artisan migrate --force`
- `php artisan config:cache route:cache view:cache event:cache`, `storage:link`
- Certbot: `certbot --nginx -d abvhps.org -d www.abvhps.org` (needs DNS from §4 to
  have propagated by now)
- Smoke test: home page, a test membership submission, the mobile app's `/api/v1/home`,
  and the WebSocket connection (home page should live-update after an admin edit)

## ⚠️ Payment gateways — confirm before real money moves

The current `.env` has **mixed signals** that must be resolved before accepting a
real donation or fee, not guessed by Claude:
- `RAZORPAY_MODE=test` but the key (`rzp_live_...`) is a **live** key.
- `CASHFREE_ENV=sandbox` / `CASHFREE_BASE_URL=https://sandbox.cashfree.com/pg`, but
  the secret key is named `cfsk_ma_prod_...` (a **prod**-scoped credential).

Claude will deploy with these exact values unchanged (server-side test/sandbox
behaviour, matching what `RAZORPAY_MODE`/`CASHFREE_ENV` currently say) so no real
charge can happen by accident. **Before announcing the site is open for donations**,
log into the Razorpay and Cashfree dashboards, copy the correct **live** key pairs
for each, and update `RAZORPAY_MODE=live` / `CASHFREE_ENV=production` together with
matching live keys — do this deliberately, not as a side effect of deployment.

## Cost estimate (ap-south-1, first 12 months on a new account)

- `t3.small` EC2: partially free-tier eligible (750 hrs/month of `t2/t3.micro` only,
  so `t3.small` is billed) — roughly ₹1,200–1,500/month
- Elastic IP: free while attached to a running instance
- S3 + data transfer: usually a few hundred rupees/month at this scale
- After 12 months, free-tier benefits end; review usage and consider a Reserved
  Instance or Savings Plan if the site is still small
