import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../features/about/models/about_model.dart';
import '../../features/blogs/models/blog_model.dart';
import '../../features/campaigns/models/campaign_model.dart';
import '../../features/certificates/models/certificate_model.dart';
import '../../features/contact/models/contact_model.dart';
import '../../features/exams/models/exam_model.dart';
import '../../features/gallery/models/gallery_model.dart';
import '../../features/projects/models/project_model.dart';
import '../../features/team/models/team_member_model.dart';
import '../../features/verification/models/verification_model.dart';
import '../../features/wings/models/wing_model.dart';
import '../auth/auth_notifier.dart';
import 'api_client.dart';

final publicApiRepositoryProvider = Provider<PublicApiRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return PublicApiRepository(apiClient: apiClient);
});

class PublicApiRepository {
  final ApiClient apiClient;

  PublicApiRepository({required this.apiClient});

  // 1. About
  Future<AboutData> getAbout() async {
    final res = await apiClient.get('/about');
    final data = res.data['data'] as Map<String, dynamic>;
    return AboutData.fromJson(data);
  }

  // 2. Team
  Future<TeamPaginatedResponse> getTeam({
    int page = 1,
    int perPage = 20,
    String? search,
    String? cadre,
    String? country,
    String? state,
    String? district,
    String? assemblySegment,
    String? mandal,
    String? panchayat,
  }) async {
    final query = <String, dynamic>{
      'page': page,
      'per_page': perPage,
    };
    if (search != null && search.isNotEmpty) query['search'] = search;
    if (cadre != null && cadre.isNotEmpty) query['cadre'] = cadre;
    if (country != null && country.isNotEmpty) query['country'] = country;
    if (state != null && state.isNotEmpty) query['state'] = state;
    if (district != null && district.isNotEmpty) query['district'] = district;
    if (assemblySegment != null && assemblySegment.isNotEmpty) query['assembly_segment'] = assemblySegment;
    if (mandal != null && mandal.isNotEmpty) query['mandal'] = mandal;
    if (panchayat != null && panchayat.isNotEmpty) query['panchayat'] = panchayat;

    final res = await apiClient.get('/team', queryParameters: query);
    return TeamPaginatedResponse.fromJson(res.data as Map<String, dynamic>);
  }

  // 3. Projects
  Future<List<ProjectModel>> getProjects() async {
    final res = await apiClient.get('/projects');
    final list = res.data['data'] as List<dynamic>? ?? [];
    return list.map((e) => ProjectModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<ProjectModel> getProject(int id) async {
    final res = await apiClient.get('/projects/$id');
    final data = res.data['data'] as Map<String, dynamic>;
    return ProjectModel.fromJson(data);
  }

  // 4. Campaigns
  Future<List<CampaignModel>> getCampaigns() async {
    final res = await apiClient.get('/campaigns');
    final list = res.data['data'] as List<dynamic>? ?? [];
    return list.map((e) => CampaignModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<CampaignModel> getCampaign(int id) async {
    final res = await apiClient.get('/campaigns/$id');
    final data = res.data['data'] as Map<String, dynamic>;
    return CampaignModel.fromJson(data);
  }

  // 5. Certificates
  Future<List<CertificateModel>> getCertificates() async {
    final res = await apiClient.get('/certificates');
    final list = res.data['data'] as List<dynamic>? ?? [];
    return list.map((e) => CertificateModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  // 6. Blogs
  Future<BlogPaginatedResponse> getBlogs({int page = 1, int perPage = 12}) async {
    final res = await apiClient.get('/blogs', queryParameters: {
      'page': page,
      'per_page': perPage,
    });
    return BlogPaginatedResponse.fromJson(res.data as Map<String, dynamic>);
  }

  Future<BlogModel> getBlog(int id) async {
    final res = await apiClient.get('/blogs/$id');
    final data = res.data['data'] as Map<String, dynamic>;
    return BlogModel.fromJson(data);
  }

  // 7. Gallery
  Future<GalleryPaginatedResponse> getGallery({int page = 1, int perPage = 24, String? type}) async {
    final query = <String, dynamic>{
      'page': page,
      'per_page': perPage,
    };
    if (type != null && type.isNotEmpty && type != 'all') {
      query['type'] = type;
    }
    final res = await apiClient.get('/gallery', queryParameters: query);
    return GalleryPaginatedResponse.fromJson(res.data as Map<String, dynamic>);
  }

  // 8. Exams
  Future<List<ExamModel>> getExams() async {
    final res = await apiClient.get('/exams');
    final list = res.data['data'] as List<dynamic>? ?? [];
    return list.map((e) => ExamModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<ExamModel> getExam(int id) async {
    final res = await apiClient.get('/exams/$id');
    final data = res.data['data'] as Map<String, dynamic>;
    return ExamModel.fromJson(data);
  }

  Future<List<ExamWinnerModel>> getExamWinners() async {
    final res = await apiClient.get('/exams/results/winners');
    final list = res.data['data'] as List<dynamic>? ?? [];
    return list.map((e) => ExamWinnerModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<ExamResultModel> searchExamResult(String hallTicket) async {
    final res = await apiClient.post('/exams/results/search', data: {
      'hall_ticket_number': hallTicket.trim(),
    });
    final data = res.data['data'] as Map<String, dynamic>?;
    if (data == null) {
      final msg = res.data['message']?.toString() ?? 'Result not published or not found.';
      throw Exception(msg);
    }
    return ExamResultModel.fromJson(data);
  }

  // 9. Wings
  Future<List<WingModel>> getWings() async {
    final res = await apiClient.get('/wings');
    final list = res.data['data'] as List<dynamic>? ?? [];
    return list.map((e) => WingModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<WingModel> getWing(String slug) async {
    final res = await apiClient.get('/wings/$slug');
    final data = res.data['data'] as Map<String, dynamic>;
    return WingModel.fromJson(data);
  }

  Future<RudrasenaEligibilityResult> verifyRudrasenaEligibility(String membershipId) async {
    final res = await apiClient.post('/wings/rudrasena/verify-eligibility', data: {
      'membership_id': membershipId.trim(),
    });
    return RudrasenaEligibilityResult.fromJson(res.data as Map<String, dynamic>);
  }

  // 10. Contact
  Future<ContactInfoModel> getContact() async {
    final res = await apiClient.get('/contact');
    final data = res.data['data'] as Map<String, dynamic>;
    return ContactInfoModel.fromJson(data);
  }

  Future<String> submitContact({
    required String name,
    required String email,
    String? phone,
    String? subject,
    required String message,
  }) async {
    final res = await apiClient.post('/contact', data: {
      'name': name.trim(),
      'email': email.trim(),
      'phone': phone?.trim() ?? '',
      'subject': subject?.trim() ?? 'Mobile App Inquiry',
      'message': message.trim(),
    });
    return res.data['message']?.toString() ?? 'Message sent successfully.';
  }

  // 11. Verification
  Future<PublicVerificationResult> verifyEntity(String type, String id) async {
    final res = await apiClient.get('/verify/$type/${id.trim()}');
    return PublicVerificationResult.fromJson(res.data as Map<String, dynamic>);
  }
}
