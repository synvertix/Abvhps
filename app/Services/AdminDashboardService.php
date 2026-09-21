<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardService
{
    /**
     * Fetch complete consolidated admin dashboard statistics and metrics.
     */
    public function getDashboardMetrics(): array
    {
        $stats = [];

        // ── CORE MEMBER COUNTS ────────────────────────────────────────────
        try { $stats['total_members']          = DB::table('memberships')->count(); }                          catch (\Throwable $e) { $stats['total_members'] = 0; }
        try { $stats['pending_memberships']    = DB::table('memberships')->where('status', 'pending')->count(); } catch (\Throwable $e) { $stats['pending_memberships'] = 0; }
        try { $stats['total_volunteers']       = DB::table('volunteers')->where('status', 'approved')->count(); } catch (\Throwable $e) { $stats['total_volunteers'] = 0; }
        try { $stats['pending_volunteers']     = DB::table('volunteers')->where('status', 'pending')->count(); } catch (\Throwable $e) { $stats['pending_volunteers'] = 0; }

        // ── RUDRA SENA ────────────────────────────────────────────────────
        try { $stats['rudrasena_count']        = DB::table('rudrasena_members')->count(); }
        catch (\Throwable $e) {
            try { $stats['rudrasena_count']    = DB::table('rudrasenas')->count(); }
            catch (\Throwable $ex) { $stats['rudrasena_count'] = 0; }
        }
        try { $stats['pending_rudrasena']      = DB::table('volunteers')->where('status', 'pending')->where('volunteer_type', 'rudrasena')->count(); } catch (\Throwable $e) { $stats['pending_rudrasena'] = 0; }

        // ── KALA BRUNDHAM ─────────────────────────────────────────────────
        try { $stats['kala_brundam_count']     = DB::table('kala_brundam_members')->count(); } catch (\Throwable $e) { $stats['kala_brundam_count'] = 0; }

        // ── GRAMA SEVA DAL ────────────────────────────────────────────────
        try { $stats['grama_seva_dal_count']   = DB::table('grama_seva_dals')->count(); }  catch (\Throwable $e) { $stats['grama_seva_dal_count'] = 0; }

        // ── ORGANIC FARMERS ───────────────────────────────────────────────
        try { $stats['organic_farmers_count']  = DB::table('organic_farmers')->count(); }  catch (\Throwable $e) { $stats['organic_farmers_count'] = 0; }

        // ── EXAMS ─────────────────────────────────────────────────────────
        try { $stats['total_exams']            = DB::table('exam_settings')->count(); }                        catch (\Throwable $e) { $stats['total_exams'] = 0; }
        try { $stats['active_exams']           = DB::table('exam_settings')->where('is_active', true)->count(); } catch (\Throwable $e) { $stats['active_exams'] = 0; }
        try { $stats['published_results']      = DB::table('exam_applications')->where('result_publication_status', 'published')->distinct('exam_setting_id')->count('exam_setting_id'); } catch (\Throwable $e) { $stats['published_results'] = 0; }
        try { $stats['pending_exam_applications'] = DB::table('exam_applications')->where('result_publication_status', '!=', 'published')->orWhereNull('result_publication_status')->count(); } catch (\Throwable $e) { $stats['pending_exam_applications'] = 0; }
        try { $stats['total_exam_applications']= DB::table('exam_applications')->count(); } catch (\Throwable $e) { $stats['total_exam_applications'] = 0; }

        // ── FUNDRAISING ───────────────────────────────────────────────────
        try { $stats['active_campaigns']       = DB::table('fundraisings')->where('is_active', true)->count(); }  catch (\Throwable $e) { try { $stats['active_campaigns'] = DB::table('fundraising_campaigns')->where('status', 'active')->count(); } catch (\Throwable $ex) { $stats['active_campaigns'] = 0; } }
        try { $stats['total_campaigns']        = DB::table('fundraisings')->count(); }                            catch (\Throwable $e) { try { $stats['total_campaigns'] = DB::table('fundraising_campaigns')->count(); } catch (\Throwable $ex) { $stats['total_campaigns'] = 0; } }
        try { $stats['total_funds_raised']     = (float)(DB::table('fundraisings')->sum('raised_amount') ?? 0); }          catch (\Throwable $e) { try { $stats['total_funds_raised'] = (float)(DB::table('fundraising_campaigns')->sum('raised_amount') ?? 0); } catch (\Throwable $ex) { $stats['total_funds_raised'] = 0; } }
        try { $stats['total_donors']           = DB::table('donations')->count(); }                               catch (\Throwable $e) { $stats['total_donors'] = 0; }

        // ── CONTENT ───────────────────────────────────────────────────────
        try { $stats['total_blogs']            = DB::table('blogs')->count(); }            catch (\Throwable $e) { $stats['total_blogs'] = 0; }
        try { $stats['published_blogs']        = DB::table('blogs')->where('status', 'published')->count(); } catch (\Throwable $e) { $stats['published_blogs'] = 0; }
        try { $stats['gallery_media']          = DB::table('galleries')->count(); }        catch (\Throwable $e) { $stats['gallery_media'] = 0; }
        try { $stats['support_cores']          = DB::table('our_supports')->count(); }     catch (\Throwable $e) { $stats['support_cores'] = 0; }

        // ── SYSTEM STATUS ─────────────────────────────────────────────────
        $dbOk = true;
        try { DB::connection()->getPdo(); } catch (\Throwable $e) { $dbOk = false; }
        $storageOk = is_writable(storage_path());

        $system = [
            'application' => 'Running',
            'application_status' => 'ok',
            'database' => $dbOk ? 'Connected' : 'Error',
            'database_status' => $dbOk ? 'ok' : 'error',
            'storage' => $storageOk ? 'Writable' : 'Check Permissions',
            'storage_status' => $storageOk ? 'ok' : 'warning',
            'total_records' => ($stats['total_members'] ?? 0) + ($stats['total_volunteers'] ?? 0),
            'total_exams' => $stats['total_exams'] ?? 0,
            'active_campaigns' => $stats['active_campaigns'] ?? 0,
        ];

        // ── RECENT AUDIT ACTIVITY (last 8 entries, safe allowlisted) ──────
        try {
            $rawActivity = DB::table('audit_logs')
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get(['action', 'actor_type', 'actor_identifier', 'target_type', 'target_id', 'created_at']);

            $recentActivity = $rawActivity->map(function ($log) {
                return [
                    'action' => str_replace('_', ' ', (string)$log->action),
                    'actor_type' => (string)($log->actor_type ?? ''),
                    'actor_identifier' => (string)($log->actor_identifier ?? ''),
                    'target_type' => (string)($log->target_type ?? ''),
                    'target_id' => $log->target_id !== null ? (string)$log->target_id : null,
                    'created_at' => $log->created_at ? Carbon::parse($log->created_at)->toIso8601String() : null,
                    'formatted_time' => $log->created_at ? Carbon::parse($log->created_at)->format('d-M H:i') : '',
                ];
            })->values()->all();
        } catch (\Throwable $e) {
            $recentActivity = [];
        }

        return [
            'stats' => $stats,
            'system' => $system,
            'recent_activity' => $recentActivity,
        ];
    }
}
