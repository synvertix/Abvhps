class AdminExamCycle {
  final int id;
  final String examTitle;
  final String? examType;
  final String examDateTime;
  final double applicationFee;
  final String status;
  final int totalApplicants;
  final int paidApplicants;
  final String? createdAt;

  const AdminExamCycle({
    required this.id,
    required this.examTitle,
    this.examType,
    required this.examDateTime,
    required this.applicationFee,
    required this.status,
    required this.totalApplicants,
    required this.paidApplicants,
    this.createdAt,
  });

  factory AdminExamCycle.fromJson(Map<String, dynamic> json) {
    return AdminExamCycle(
      id: json['id'] as int,
      examTitle: json['exam_title'] as String? ?? '',
      examType: json['exam_type'] as String?,
      examDateTime: json['exam_date_time'] as String? ?? '',
      applicationFee: (json['application_fee'] as num?)?.toDouble() ?? 0.0,
      status: json['status'] as String? ?? 'active',
      totalApplicants: json['total_applicants'] as int? ?? 0,
      paidApplicants: json['paid_applicants'] as int? ?? 0,
      createdAt: json['created_at'] as String?,
    );
  }
}

class AdminExamApplicant {
  final int id;
  final String fullName;
  final String hallTicketNumber;
  final String email;
  final String? guardianMobileOrId;
  final String? schoolCollegeName;
  final String paymentStatus;
  final int? marksObtained;
  final int? totalMarks;
  final String? grade;
  final String resultStatus;
  final String resultPublicationStatus;
  final int? winnerRank;
  final String? prizeTitleWon;

  const AdminExamApplicant({
    required this.id,
    required this.fullName,
    required this.hallTicketNumber,
    required this.email,
    this.guardianMobileOrId,
    this.schoolCollegeName,
    required this.paymentStatus,
    this.marksObtained,
    this.totalMarks,
    this.grade,
    required this.resultStatus,
    required this.resultPublicationStatus,
    this.winnerRank,
    this.prizeTitleWon,
  });

  factory AdminExamApplicant.fromJson(Map<String, dynamic> json) {
    return AdminExamApplicant(
      id: json['id'] as int,
      fullName: json['full_name'] as String? ?? '',
      hallTicketNumber: json['hall_ticket_number'] as String? ?? '',
      email: json['email'] as String? ?? '',
      guardianMobileOrId: json['guardian_mobile_or_id'] as String?,
      schoolCollegeName: json['school_college_name'] as String?,
      paymentStatus: json['payment_status'] as String? ?? 'pending',
      marksObtained: json['marks_obtained'] as int?,
      totalMarks: json['total_marks'] as int?,
      grade: json['grade'] as String?,
      resultStatus: json['result_status'] as String? ?? 'pending',
      resultPublicationStatus: json['result_publication_status'] as String? ?? 'draft',
      winnerRank: json['winner_rank'] as int?,
      prizeTitleWon: json['prize_title_won'] as String?,
    );
  }
}
