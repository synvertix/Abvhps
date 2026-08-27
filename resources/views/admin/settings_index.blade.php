<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Global Settings | ABVHPS Central Board</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-brandOrange: #FF6600;
            --color-brandGray: #4A4A4A;
            --color-brandDarkGray: #1A1A1A;
            --color-brandLightOrange: #FFF5EE;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800 flex h-screen overflow-hidden">

    <!-- BLOCK 1: MASTER UNIFIED CENTRAL ADMIN SIDEBAR -->
    @include('admin.partials.sidebar')

    <!-- BLOCK 2: WORKSPACE -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2 min-w-0 max-w-full">
                @include('admin.partials.header_button')
                <span class="text-xs sm:text-sm font-black text-brandGray uppercase tracking-wider shrink-0">Module:</span>
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm break-words whitespace-normal leading-tight">Global Configuration &amp; Site Settings</span>
            </div>
            <div class="text-right text-[10px] font-mono font-black text-gray-500">
                Real-Time Config Engine
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 space-y-6">
            @if(session('success'))
                <div class="p-3 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-bold flex items-center justify-between">
                    <span>✓ {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 font-black">×</button>
                </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-4xl">
                @csrf

                <!-- Organization Contact Settings -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4 text-xs">
                    <div class="border-b border-gray-100 pb-2">
                        <h3 class="font-black text-sm text-gray-900 uppercase">Organization Contact Information</h3>
                        <p class="text-[10px] text-gray-500">Displayed in top header bar, footer, and contact page.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Helpline Phone Number *</label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                        </div>
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Official Email Address *</label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-black text-gray-700 uppercase mb-1">Headquarters Physical Address *</label>
                        <textarea name="contact_address" rows="2" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">{{ old('contact_address', $settings['contact_address']) }}</textarea>
                    </div>
                </div>

                <!-- WhatsApp Contact -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4 text-xs">
                    <div class="border-b border-gray-100 pb-2 flex items-center justify-between">
                        <div>
                            <h3 class="font-black text-sm text-gray-900 uppercase flex items-center gap-2">
                                <span class="text-emerald-500">💬</span> WHATSAPP CONTACT
                            </h3>
                            <p class="text-[10px] text-gray-500">Live official WhatsApp number configuration for public website & admin support links.</p>
                        </div>
                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-black px-2.5 py-1 rounded-full border border-emerald-200 uppercase">Live Integration</span>
                    </div>

                    <div class="max-w-md">
                        <label class="block font-black text-gray-700 uppercase mb-1">WhatsApp Number *</label>
                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '+91 9989980055') }}" placeholder="e.g. +91 9989980055" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                        <p class="text-[10px] text-gray-500 mt-1.5 font-medium">
                            This number is used by the website WhatsApp contact buttons. Administrators can update it here without changing the code.
                        </p>
                    </div>
                </div>

                <!-- Social Media URLs -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4 text-xs">
                    <div class="border-b border-gray-100 pb-2">
                        <h3 class="font-black text-sm text-gray-900 uppercase">Social Media Links</h3>
                        <p class="text-[10px] text-gray-500">Synced to header bar social links and footer.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Facebook URL</label>
                            <input type="url" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url']) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                        </div>
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Twitter / X URL</label>
                            <input type="url" name="twitter_url" value="{{ old('twitter_url', $settings['twitter_url']) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                        </div>
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">YouTube URL</label>
                            <input type="url" name="youtube_url" value="{{ old('youtube_url', $settings['youtube_url']) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                        </div>
                    </div>
                </div>

                <!-- Brand Assets & Media Uploads -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4 text-xs">
                    <div class="border-b border-gray-100 pb-2">
                        <h3 class="font-black text-sm text-gray-900 uppercase">Brand Media & Assets</h3>
                        <p class="text-[10px] text-gray-500">Update the site wordmark logo and browser favicon.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Upload Site Logo (PNG)</label>
                            <input type="file" name="site_logo" accept="image/png,image/jpeg" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-600">
                            <span class="text-[10px] text-gray-400">Transparent PNG recommended (Max: 2MB)</span>
                        </div>
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Upload Site Favicon</label>
                            <input type="file" name="site_favicon" accept="image/png,image/x-icon,image/vnd.microsoft.icon" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-600">
                            <span class="text-[10px] text-gray-400">Square PNG or ICO (Max: 1MB)</span>
                        </div>
                    </div>
                </div>

                <!-- Homepage Join / Membership Floating Strip Settings -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4 text-xs">
                    <div class="border-b border-gray-100 pb-2">
                        <h3 class="font-black text-sm text-gray-900 uppercase flex items-center gap-2">
                            <span class="text-brandOrange">🤝</span> HOMEPAGE FLOATING JOIN &amp; MEMBERSHIP STRIP
                        </h3>
                        <p class="text-[11px] text-gray-500">Configure the overlapping floating strip between Vision/Mission and Statistics on the homepage.</p>
                    </div>

                    <!-- Section Enabled Toggle -->
                    <div>
                        <label class="block font-black text-gray-700 uppercase mb-1">Section Enabled *</label>
                        <select name="homepage_join_enabled" class="w-full sm:w-64 border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                            <option value="1" {{ in_array($settings['homepage_join_enabled'] ?? '1', ['1', 'yes', true], true) ? 'selected' : '' }}>Yes (Enabled on Homepage)</option>
                            <option value="0" {{ in_array($settings['homepage_join_enabled'] ?? '1', ['0', 'no', false], true) ? 'selected' : '' }}>No (Hidden from Homepage)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                        <!-- Left Side: Why Join -->
                        <div class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <h4 class="font-black text-xs text-brandGray uppercase tracking-wider">Left Side — Why Join ABVHPS?</h4>
                            <div>
                                <label class="block font-bold text-gray-700 uppercase mb-1 text-[11px]">Why Join Heading</label>
                                <input type="text" name="homepage_join_why_heading" value="{{ old('homepage_join_why_heading', $settings['homepage_join_why_heading']) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 uppercase mb-1 text-[11px]">Why Join Description</label>
                                <textarea name="homepage_join_why_text" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-medium text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">{{ old('homepage_join_why_text', $settings['homepage_join_why_text']) }}</textarea>
                            </div>
                        </div>

                        <!-- Right Side: Membership CTA -->
                        <div class="space-y-3 bg-orange-50/40 p-4 rounded-xl border border-orange-200">
                            <h4 class="font-black text-xs text-brandOrange uppercase tracking-wider">Right Side — Membership CTA</h4>
                            <div>
                                <label class="block font-bold text-gray-700 uppercase mb-1 text-[11px]">Membership Heading</label>
                                <input type="text" name="homepage_join_member_heading" value="{{ old('homepage_join_member_heading', $settings['homepage_join_member_heading']) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 uppercase mb-1 text-[11px]">Membership Description</label>
                                <textarea name="homepage_join_member_text" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-medium text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">{{ old('homepage_join_member_text', $settings['homepage_join_member_text']) }}</textarea>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1 text-[11px]">CTA Button Text</label>
                                    <input type="text" name="homepage_join_cta_text" value="{{ old('homepage_join_cta_text', $settings['homepage_join_cta_text']) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 uppercase mb-1 text-[11px]">CTA Destination (Locked)</label>
                                    <div class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono font-bold text-gray-600 truncate" title="{{ route('membership.form') }}">
                                        {{ route('membership.form') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Homepage Supporting Partners & Logo Manager -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-5 text-xs">
                    <div class="border-b border-gray-100 pb-2 flex items-center justify-between">
                        <div>
                            <h3 class="font-black text-sm text-gray-900 uppercase flex items-center gap-2">
                                <span class="text-brandOrange">🤝</span> SUPPORTING PARTNERS &amp; LOGO MANAGER
                            </h3>
                            <p class="text-[11px] text-gray-500">Manage the compact continuous supporting partners scrolling ticker and brand logos.</p>
                        </div>
                        <span class="bg-orange-50 text-brandOrange text-[10px] font-black px-2.5 py-1 rounded-full border border-orange-200 uppercase">Brand Ticker</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Section Enabled *</label>
                            <select name="homepage_sponsors_enabled" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                                <option value="1" {{ in_array($settings['homepage_sponsors_enabled'] ?? '1', ['1', 'yes', true], true) ? 'selected' : '' }}>Yes (Enabled on Homepage)</option>
                                <option value="0" {{ in_array($settings['homepage_sponsors_enabled'] ?? '1', ['0', 'no', false], true) ? 'selected' : '' }}>No (Hidden from Homepage)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Section Heading *</label>
                            <input type="text" name="homepage_sponsors_heading" value="{{ old('homepage_sponsors_heading', $settings['homepage_sponsors_heading']) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                        </div>
                    </div>

                    <!-- Current Supporting Partners List -->
                    <div class="space-y-3 pt-2 border-t border-gray-100">
                        <label class="block font-black text-gray-800 uppercase tracking-wider text-[11px]">Current Supporting Partners ({{ count($settings['supporting_partners'] ?? []) }})</label>

                        <div class="space-y-3" id="partners-list-container">
                            @foreach($settings['supporting_partners'] ?? [] as $partner)
                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 hover:border-orange-200 transition space-y-3">
                                    <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                                        <span class="font-extrabold text-[11px] text-gray-700 uppercase tracking-wider">Partner #{{ $loop->iteration }}</span>
                                        <label class="inline-flex items-center gap-1.5 text-[11px] text-red-600 font-bold cursor-pointer hover:text-red-700">
                                            <input type="checkbox" name="remove_partner_ids[]" value="{{ $partner['id'] }}" class="rounded text-red-600 focus:ring-red-500">
                                            <span>Remove Partner</span>
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                                        <!-- Partner Name -->
                                        <div class="md:col-span-5">
                                            <label class="block font-bold text-gray-700 uppercase mb-1 text-[10px]">Partner Name *</label>
                                            <input type="text" name="partners[{{ $partner['id'] }}][name]" value="{{ old('partners.'.$partner['id'].'.name', $partner['name']) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                                        </div>

                                        <!-- Partner Logo Preview & Upload -->
                                        <div class="md:col-span-5 space-y-1.5">
                                            <label class="block font-bold text-gray-700 uppercase text-[10px]">Logo (PNG, JPG, WEBP, Max 2MB)</label>
                                            <input type="hidden" name="partners[{{ $partner['id'] }}][existing_logo_path]" value="{{ $partner['logo_path'] ?? '' }}">

                                            <div class="flex items-center gap-3">
                                                @if(!empty($partner['logo_path']))
                                                    <div class="h-8 max-w-[80px] bg-white p-1 rounded border border-gray-300 flex items-center justify-center shrink-0">
                                                        <img src="{{ asset('storage/' . $partner['logo_path']) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                                                    </div>
                                                    <label class="inline-flex items-center gap-1 text-[10px] text-red-600 font-bold cursor-pointer hover:underline">
                                                        <input type="checkbox" name="remove_logo_ids[]" value="{{ $partner['id'] }}" class="rounded text-red-600 focus:ring-red-500">
                                                        <span>Remove Logo</span>
                                                    </label>
                                                @else
                                                    <span class="text-[10px] text-gray-400 font-medium italic">No logo (Text-only)</span>
                                                @endif
                                            </div>

                                            <div>
                                                <input type="file" name="partner_logos[{{ $partner['id'] }}]" accept="image/png,image/jpeg,image/jpg,image/webp" class="block w-full text-[10px] text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-orange-50 file:text-brandOrange hover:file:bg-orange-100">
                                            </div>
                                        </div>

                                        <!-- Display Order -->
                                        <div class="md:col-span-2">
                                            <label class="block font-bold text-gray-700 uppercase mb-1 text-[10px]">Order</label>
                                            <input type="number" name="partners[{{ $partner['id'] }}][order]" value="{{ old('partners.'.$partner['id'].'.order', $partner['order'] ?? $loop->iteration) }}" min="1" class="w-full border border-gray-300 rounded-lg px-2.5 py-2 text-xs font-bold text-gray-800 text-center focus:ring-2 focus:ring-brandOrange outline-none">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Add New Supporting Partner Block -->
                    <div class="bg-orange-50/50 p-4 rounded-xl border border-orange-200 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="text-brandOrange font-black text-sm">+</span>
                            <h4 class="font-extrabold text-xs text-brandOrange uppercase tracking-wider">Add New Supporting Partner</h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                            <div class="md:col-span-5">
                                <label class="block font-bold text-gray-700 uppercase mb-1 text-[10px]">New Partner Name</label>
                                <input type="text" name="new_partner_name" placeholder="e.g. Acme Foundation" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none bg-white">
                            </div>
                            <div class="md:col-span-5">
                                <label class="block font-bold text-gray-700 uppercase mb-1 text-[10px]">New Partner Logo (Optional)</label>
                                <input type="file" name="new_partner_logo" accept="image/png,image/jpeg,image/jpg,image/webp" class="block w-full text-[10px] text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-white file:text-brandOrange hover:file:bg-orange-100">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block font-bold text-gray-700 uppercase mb-1 text-[10px]">Order</label>
                                <input type="number" name="new_partner_order" value="{{ count($settings['supporting_partners'] ?? []) + 1 }}" min="1" class="w-full border border-gray-300 rounded-lg px-2.5 py-2 text-xs font-bold text-gray-800 text-center focus:ring-2 focus:ring-brandOrange outline-none bg-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO & Footer Descriptions -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4 text-xs">
                    <div class="border-b border-gray-100 pb-2">
                        <h3 class="font-black text-sm text-gray-900 uppercase">SEO & Footer Descriptions</h3>
                    </div>

                    <div>
                        <label class="block font-black text-gray-700 uppercase mb-1">Global Site Title *</label>
                        <input type="text" name="site_title" value="{{ old('site_title', $settings['site_title']) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                    </div>

                    <div>
                        <label class="block font-black text-gray-700 uppercase mb-1">Footer "About ABVHPS" Description *</label>
                        <textarea name="footer_about" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">{{ old('footer_about', $settings['footer_about']) }}</textarea>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-xs px-8 py-3 rounded-xl shadow uppercase tracking-wider transition">
                        Save Global Settings
                    </button>
                </div>
            </form>
        </main>
    </div>

    <!-- Floating WhatsApp Quick Connect Button -->
    <x-whatsapp-floating-button />

</body>
</html>
