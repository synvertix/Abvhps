import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../core/auth/auth_notifier.dart';
import '../features/about/about_screen.dart';
import '../features/auth/login_screen.dart';
import '../features/auth/volunteer_change_password_screen.dart';
import '../features/blogs/blog_detail_screen.dart';
import '../features/blogs/blogs_screen.dart';
import '../features/campaigns/campaign_detail_screen.dart';
import '../features/campaigns/campaigns_list_screen.dart';
import '../features/certificates/compliance_certificates_screen.dart';
import '../features/contact/contact_screen.dart';
import '../features/exams/exam_detail_screen.dart';
import '../features/exams/exam_results_screen.dart';
import '../features/exams/exams_notice_board_screen.dart';
import '../features/gallery/gallery_screen.dart';
import '../features/home/home_screen.dart';
import '../features/member/member_dashboard_screen.dart';
import '../features/projects/project_detail_screen.dart';
import '../features/projects/projects_list_screen.dart';
import '../features/team/our_team_screen.dart';
import '../features/verification/verification_screen.dart';
import '../features/volunteer/volunteer_dashboard_screen.dart';
import '../features/wings/wing_detail_screen.dart';
import '../features/wings/wings_list_screen.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  return GoRouter(
    initialLocation: '/',
    refreshListenable: GoRouterRefreshStream(ref.watch(authNotifierProvider.notifier).stream),
    redirect: (context, state) {
      final authState = ref.read(authNotifierProvider);
      final loc = state.matchedLocation;
      final isLoggingIn = loc == '/login';

      if (authState.isLoading) {
        return null;
      }

      // Protected authenticated-only route prefixes
      final isProtectedRoute = loc.startsWith('/volunteer') || loc.startsWith('/member');

      if (!authState.isAuthenticated) {
        if (isProtectedRoute) {
          return '/login';
        }
        return null; // Public routes allowed for everyone
      }

      // User IS authenticated
      if (authState.mustChangePassword) {
        if (loc != '/volunteer/change-password') {
          return '/volunteer/change-password';
        }
        return null;
      }

      // If already logged in and at /login, /, or change-password, redirect to dashboard
      if (isLoggingIn || loc == '/' || loc == '/volunteer/change-password') {
        if (authState.accountType == 'volunteer') {
          return '/volunteer/dashboard';
        } else if (authState.accountType == 'member') {
          return '/member/dashboard';
        }
      }

      return null;
    },
    routes: [
      // 1. Home
      GoRoute(
        path: '/',
        builder: (context, state) => const HomeScreen(),
      ),

      // 2. About
      GoRoute(
        path: '/about',
        builder: (context, state) => const AboutScreen(),
      ),

      // 3. Team Directory
      GoRoute(
        path: '/team',
        builder: (context, state) => const OurTeamScreen(),
      ),

      // 4. Media Gallery
      GoRoute(
        path: '/gallery',
        builder: (context, state) => const GalleryScreen(),
      ),

      // 5. Blogs (Static list before dynamic :id)
      GoRoute(
        path: '/blogs',
        builder: (context, state) => const BlogsScreen(),
      ),
      GoRoute(
        path: '/blogs/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
          return BlogDetailScreen(blogId: id);
        },
      ),

      // 6. Projects (Static list before dynamic :id)
      GoRoute(
        path: '/projects',
        builder: (context, state) => const ProjectsListScreen(),
      ),
      GoRoute(
        path: '/projects/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
          return ProjectDetailScreen(projectId: id);
        },
      ),

      // 7. Campaigns (Static list before dynamic :id)
      GoRoute(
        path: '/campaigns',
        builder: (context, state) => const CampaignsListScreen(),
      ),
      GoRoute(
        path: '/campaigns/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
          return CampaignDetailScreen(campaignId: id);
        },
      ),

      // 8. 80G & 12A Compliance Certificates
      GoRoute(
        path: '/certificates',
        builder: (context, state) => const ComplianceCertificatesScreen(),
      ),

      // 9. Exams (Static sub-routes declared BEFORE dynamic :id)
      GoRoute(
        path: '/exams/board',
        builder: (context, state) => const ExamsNoticeBoardScreen(),
      ),
      GoRoute(
        path: '/exams/results',
        builder: (context, state) => const ExamResultsScreen(),
      ),
      GoRoute(
        path: '/exams/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
          return ExamDetailScreen(examId: id);
        },
      ),

      // 10. Wings (Static list before dynamic :slug)
      GoRoute(
        path: '/wings',
        builder: (context, state) => const WingsListScreen(),
      ),
      GoRoute(
        path: '/wings/:slug',
        builder: (context, state) {
          final slug = state.pathParameters['slug'] ?? 'rudrasena';
          return WingDetailScreen(slug: slug);
        },
      ),

      // 11. Contact
      GoRoute(
        path: '/contact',
        builder: (context, state) => const ContactScreen(),
      ),

      // 12. QR / Master ID Public Verification
      GoRoute(
        path: '/verify',
        builder: (context, state) {
          final type = state.uri.queryParameters['type'];
          final id = state.uri.queryParameters['id'];
          return VerificationScreen(initialType: type, initialId: id);
        },
      ),

      // 13. Authentication & Protected Routes
      GoRoute(
        path: '/login',
        builder: (context, state) {
          final type = state.uri.queryParameters['type'] ?? 'volunteer';
          return LoginScreen(initialType: type);
        },
      ),
      GoRoute(
        path: '/volunteer/change-password',
        builder: (context, state) => const VolunteerChangePasswordScreen(),
      ),
      GoRoute(
        path: '/volunteer/dashboard',
        builder: (context, state) => const VolunteerDashboardScreen(),
      ),
      GoRoute(
        path: '/member/dashboard',
        builder: (context, state) => const MemberDashboardScreen(),
      ),
    ],
  );
});

class GoRouterRefreshStream extends ChangeNotifier {
  GoRouterRefreshStream(Stream<dynamic> stream) {
    stream.listen((_) => notifyListeners());
  }
}
