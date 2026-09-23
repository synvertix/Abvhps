class AdminUser {
  final String name;
  final String email;

  const AdminUser({required this.name, required this.email});

  factory AdminUser.fromJson(Map<String, dynamic> json) {
    return AdminUser(
      name: json['name'] as String? ?? 'Administrator',
      email: json['email'] as String? ?? '',
    );
  }
}

class AdminSummary {
  final int totalProfiles;
  final int volunteers;
  final int pendingActions;
  final double fundsRaised;

  const AdminSummary({
    required this.totalProfiles,
    required this.volunteers,
    required this.pendingActions,
    required this.fundsRaised,
  });

  factory AdminSummary.fromJson(Map<String, dynamic> json) {
    return AdminSummary(
      totalProfiles: (json['total_profiles'] as num?)?.toInt() ?? 0,
      volunteers: (json['volunteers'] as num?)?.toInt() ?? 0,
      pendingActions: (json['pending_actions'] as num?)?.toInt() ?? 0,
      fundsRaised: (json['funds_raised'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class AdminWings {
  final int centralBase;
  final int rudraSena;
  final int kalaBrundham;
  final int gramaSevaDal;
  final int organicFarmers;
  final int dharmaSeva;

  const AdminWings({
    required this.centralBase,
    required this.rudraSena,
    required this.kalaBrundham,
    required this.gramaSevaDal,
    required this.organicFarmers,
    required this.dharmaSeva,
  });

  factory AdminWings.fromJson(Map<String, dynamic> json) {
    return AdminWings(
      centralBase: (json['central_base'] as num?)?.toInt() ?? 0,
      rudraSena: (json['rudra_sena'] as num?)?.toInt() ?? 0,
      kalaBrundham: (json['kala_brundham'] as num?)?.toInt() ?? 0,
      gramaSevaDal: (json['grama_seva_dal'] as num?)?.toInt() ?? 0,
      organicFarmers: (json['organic_farmers'] as num?)?.toInt() ?? 0,
      dharmaSeva: (json['dharma_seva'] as num?)?.toInt() ?? 0,
    );
  }
}

class AdminPending {
  final int volunteers;
  final int memberships;
  final int examApplications;
  final int resultsPublished;
  final int activeCampaigns;

  const AdminPending({
    required this.volunteers,
    required this.memberships,
    required this.examApplications,
    required this.resultsPublished,
    required this.activeCampaigns,
  });

  factory AdminPending.fromJson(Map<String, dynamic> json) {
    return AdminPending(
      volunteers: (json['volunteers'] as num?)?.toInt() ?? 0,
      memberships: (json['memberships'] as num?)?.toInt() ?? 0,
      examApplications: (json['exam_applications'] as num?)?.toInt() ?? 0,
      resultsPublished: (json['results_published'] as num?)?.toInt() ?? 0,
      activeCampaigns: (json['active_campaigns'] as num?)?.toInt() ?? 0,
    );
  }
}

class AdminSystem {
  final String application;
  final String database;
  final String storage;
  final int totalRecords;
  final int totalExams;
  final int activeCampaigns;
  final String databaseStatus;
  final String storageStatus;

  const AdminSystem({
    required this.application,
    required this.database,
    required this.storage,
    required this.totalRecords,
    required this.totalExams,
    required this.activeCampaigns,
    required this.databaseStatus,
    required this.storageStatus,
  });

  factory AdminSystem.fromJson(Map<String, dynamic> json) {
    return AdminSystem(
      application: json['application'] as String? ?? 'Running',
      database: json['database'] as String? ?? 'Connected',
      storage: json['storage'] as String? ?? 'Writable',
      totalRecords: (json['total_records'] as num?)?.toInt() ?? 0,
      totalExams: (json['total_exams'] as num?)?.toInt() ?? 0,
      activeCampaigns: (json['active_campaigns'] as num?)?.toInt() ?? 0,
      databaseStatus: json['database_status'] as String? ?? 'ok',
      storageStatus: json['storage_status'] as String? ?? 'ok',
    );
  }
}

class AdminExams {
  final int total;
  final int active;
  final int applications;
  final int resultsPublished;

  const AdminExams({
    required this.total,
    required this.active,
    required this.applications,
    required this.resultsPublished,
  });

  factory AdminExams.fromJson(Map<String, dynamic> json) {
    return AdminExams(
      total: (json['total'] as num?)?.toInt() ?? 0,
      active: (json['active'] as num?)?.toInt() ?? 0,
      applications: (json['applications'] as num?)?.toInt() ?? 0,
      resultsPublished: (json['results_published'] as num?)?.toInt() ?? 0,
    );
  }
}

class AdminFundraising {
  final int totalCampaigns;
  final int activeCampaigns;
  final int totalDonors;
  final double amountRaised;

  const AdminFundraising({
    required this.totalCampaigns,
    required this.activeCampaigns,
    required this.totalDonors,
    required this.amountRaised,
  });

  factory AdminFundraising.fromJson(Map<String, dynamic> json) {
    return AdminFundraising(
      totalCampaigns: (json['total_campaigns'] as num?)?.toInt() ?? 0,
      activeCampaigns: (json['active_campaigns'] as num?)?.toInt() ?? 0,
      totalDonors: (json['total_donors'] as num?)?.toInt() ?? 0,
      amountRaised: (json['amount_raised'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class AdminContent {
  final int blogs;
  final int publishedBlogs;
  final int galleryMedia;
  final int supportCores;

  const AdminContent({
    required this.blogs,
    required this.publishedBlogs,
    required this.galleryMedia,
    required this.supportCores,
  });

  factory AdminContent.fromJson(Map<String, dynamic> json) {
    return AdminContent(
      blogs: (json['blogs'] as num?)?.toInt() ?? 0,
      publishedBlogs: (json['published_blogs'] as num?)?.toInt() ?? 0,
      galleryMedia: (json['gallery_media'] as num?)?.toInt() ?? 0,
      supportCores: (json['support_cores'] as num?)?.toInt() ?? 0,
    );
  }
}

class AdminActivity {
  final String action;
  final String actorType;
  final String actorIdentifier;
  final String targetType;
  final String? targetId;
  final String? createdAt;
  final String formattedTime;

  const AdminActivity({
    required this.action,
    required this.actorType,
    required this.actorIdentifier,
    required this.targetType,
    this.targetId,
    this.createdAt,
    required this.formattedTime,
  });

  factory AdminActivity.fromJson(Map<String, dynamic> json) {
    return AdminActivity(
      action: json['action'] as String? ?? '',
      actorType: json['actor_type'] as String? ?? '',
      actorIdentifier: json['actor_identifier'] as String? ?? '',
      targetType: json['target_type'] as String? ?? '',
      targetId: json['target_id'] as String?,
      createdAt: json['created_at'] as String?,
      formattedTime: json['formatted_time'] as String? ?? '',
    );
  }
}

class AdminDashboardData {
  final AdminUser administrator;
  final AdminSummary summary;
  final AdminWings wings;
  final AdminPending pending;
  final AdminSystem system;
  final AdminExams exams;
  final AdminFundraising fundraising;
  final AdminContent content;
  final List<AdminActivity> recentActivity;

  const AdminDashboardData({
    required this.administrator,
    required this.summary,
    required this.wings,
    required this.pending,
    required this.system,
    required this.exams,
    required this.fundraising,
    required this.content,
    required this.recentActivity,
  });

  factory AdminDashboardData.fromJson(Map<String, dynamic> json) {
    return AdminDashboardData(
      administrator: AdminUser.fromJson(json['administrator'] as Map<String, dynamic>? ?? {}),
      summary: AdminSummary.fromJson(json['summary'] as Map<String, dynamic>? ?? {}),
      wings: AdminWings.fromJson(json['wings'] as Map<String, dynamic>? ?? {}),
      pending: AdminPending.fromJson(json['pending'] as Map<String, dynamic>? ?? {}),
      system: AdminSystem.fromJson(json['system'] as Map<String, dynamic>? ?? {}),
      exams: AdminExams.fromJson(json['exams'] as Map<String, dynamic>? ?? {}),
      fundraising: AdminFundraising.fromJson(json['fundraising'] as Map<String, dynamic>? ?? {}),
      content: AdminContent.fromJson(json['content'] as Map<String, dynamic>? ?? {}),
      recentActivity: (json['recent_activity'] as List<dynamic>?)
              ?.map((e) => AdminActivity.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}
