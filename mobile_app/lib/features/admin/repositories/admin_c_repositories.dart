import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/core/auth/auth_notifier.dart';
import 'package:abvhpsapp/features/admin/models/admin_team_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_donation_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_blog_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_gallery_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_support_core_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_exam_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_campaign_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_contact_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_tax_certificate_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_site_settings_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_banner_model.dart';

final adminTeamProvider = FutureProvider.family.autoDispose<List<AdminTeamMember>, String>((ref, search) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/team', queryParameters: {'search': search});
  final list = (response.data['data'] as List).map((x) => AdminTeamMember.fromJson(x as Map<String, dynamic>)).toList();
  return list;
});

final adminDonationsProvider = FutureProvider.family.autoDispose<List<AdminDonation>, String>((ref, search) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/donations', queryParameters: {'search': search});
  final list = (response.data['data'] as List).map((x) => AdminDonation.fromJson(x as Map<String, dynamic>)).toList();
  return list;
});

final adminBlogsProvider = FutureProvider.family.autoDispose<List<AdminBlog>, String>((ref, search) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/blogs', queryParameters: {'search': search});
  final list = (response.data['data'] as List).map((x) => AdminBlog.fromJson(x as Map<String, dynamic>)).toList();
  return list;
});

final adminGalleryProvider = FutureProvider.autoDispose<List<AdminGalleryItem>>((ref) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/gallery');
  final list = (response.data['data'] as List).map((x) => AdminGalleryItem.fromJson(x as Map<String, dynamic>)).toList();
  return list;
});

final adminSupportCoresProvider = FutureProvider.family.autoDispose<List<AdminSupportCore>, String>((ref, search) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/support-cores', queryParameters: {'search': search});
  final list = (response.data['data'] as List).map((x) => AdminSupportCore.fromJson(x as Map<String, dynamic>)).toList();
  return list;
});

final adminExamsProvider = FutureProvider.autoDispose<List<AdminExamCycle>>((ref) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/exams');
  final list = (response.data['data'] as List).map((x) => AdminExamCycle.fromJson(x as Map<String, dynamic>)).toList();
  return list;
});

final adminFundraisingProvider = FutureProvider.family.autoDispose<List<AdminCampaign>, String>((ref, search) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/fundraising', queryParameters: {'search': search});
  final list = (response.data['data'] as List).map((x) => AdminCampaign.fromJson(x as Map<String, dynamic>)).toList();
  return list;
});

final adminContactsProvider = FutureProvider.family.autoDispose<List<AdminContactMessage>, String>((ref, search) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/contacts', queryParameters: {'search': search});
  final list = (response.data['data'] as List).map((x) => AdminContactMessage.fromJson(x as Map<String, dynamic>)).toList();
  return list;
});

final adminTaxCertificatesProvider = FutureProvider.family.autoDispose<List<AdminTaxCertificate>, String>((ref, search) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/tax-certificates', queryParameters: {'search': search});
  final list = (response.data['data'] as List).map((x) => AdminTaxCertificate.fromJson(x as Map<String, dynamic>)).toList();
  return list;
});

final adminSiteSettingsProvider = FutureProvider.autoDispose<AdminSiteSettings>((ref) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/settings');
  return AdminSiteSettings.fromJson(response.data['data'] as Map<String, dynamic>);
});

final adminBannersProvider = FutureProvider.family.autoDispose<List<AdminBanner>, String>((ref, search) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.get('/admin/banners', queryParameters: {'search': search});
  final list = (response.data['data'] as List).map((x) => AdminBanner.fromJson(x as Map<String, dynamic>)).toList();
  return list;
});
