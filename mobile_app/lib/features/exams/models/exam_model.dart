class ExamModel {
  final int id;
  final String examTitle;
  final String? examType;
  final String? examTypeLabel;
  final String? examDateTime;
  final String? examCenterLocation;
  final double applicationFee;
  final String status;
  final String? bannerImageUrl;
  final String? syllabusUrl;
  final List<String> prizes;
  final String? guidelines;

  const ExamModel({
    required this.id,
    required this.examTitle,
    this.examType,
    this.examTypeLabel,
    this.examDateTime,
    this.examCenterLocation,
    required this.applicationFee,
    required this.status,
    this.bannerImageUrl,
    this.syllabusUrl,
    this.prizes = const [],
    this.guidelines,
  });

  factory ExamModel.fromJson(Map<String, dynamic> json) {
    return ExamModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      examTitle: json['exam_title']?.toString() ?? 'Sanathana Dharma Examination',
      examType: json['exam_type']?.toString(),
      examTypeLabel: json['exam_type_label']?.toString() ?? 'Examination',
      examDateTime: json['exam_date_time']?.toString(),
      examCenterLocation: json['exam_center_location']?.toString(),
      applicationFee: (json['application_fee'] as num?)?.toDouble() ?? 0.0,
      status: json['status']?.toString() ?? 'active',
      bannerImageUrl: json['banner_image_url']?.toString(),
      syllabusUrl: json['syllabus_url']?.toString(),
      prizes: (json['prizes'] as List<dynamic>? ?? [])
          .map((e) => e.toString())
          .toList(),
      guidelines: json['guidelines']?.toString(),
    );
  }
}

class ExamWinnerModel {
  final int id;
  final int winnerRank;
  final String fullName;
  final String? schoolName;
  final String examTitle;
  final String? prizeTitleWon;
  final String? grade;
  final String? photoUrl;

  const ExamWinnerModel({
    required this.id,
    required this.winnerRank,
    required this.fullName,
    this.schoolName,
    required this.examTitle,
    this.prizeTitleWon,
    this.grade,
    this.photoUrl,
  });

  factory ExamWinnerModel.fromJson(Map<String, dynamic> json) {
    return ExamWinnerModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      winnerRank: (json['winner_rank'] as num?)?.toInt() ?? 1,
      fullName: json['full_name']?.toString() ?? 'Winner Candidate',
      schoolName: json['school_name']?.toString(),
      examTitle: json['exam_title']?.toString() ?? 'Sanathana Dharma Exam',
      prizeTitleWon: json['prize_title_won']?.toString(),
      grade: json['grade']?.toString(),
      photoUrl: json['photo_url']?.toString(),
    );
  }
}

class ExamResultModel {
  final String fullName;
  final String hallTicket;
  final String? schoolName;
  final String examTitle;
  final String? examType;
  final String? examDate;
  final num? marksObtained;
  final num? totalMarks;
  final num? percentage;
  final String? grade;
  final String? status;
  final String? prizeTitleWon;

  const ExamResultModel({
    required this.fullName,
    required this.hallTicket,
    this.schoolName,
    required this.examTitle,
    this.examType,
    this.examDate,
    this.marksObtained,
    this.totalMarks,
    this.percentage,
    this.grade,
    this.status,
    this.prizeTitleWon,
  });

  factory ExamResultModel.fromJson(Map<String, dynamic> json) {
    return ExamResultModel(
      fullName: json['full_name']?.toString() ?? 'Candidate',
      hallTicket: json['hall_ticket']?.toString() ?? '',
      schoolName: json['school_name']?.toString(),
      examTitle: json['exam_title']?.toString() ?? 'Sanathana Dharma Exam',
      examType: json['exam_type']?.toString(),
      examDate: json['exam_date']?.toString(),
      marksObtained: json['marks_obtained'] as num?,
      totalMarks: json['total_marks'] as num?,
      percentage: json['percentage'] as num?,
      grade: json['grade']?.toString(),
      status: json['status']?.toString() ?? 'Passed',
      prizeTitleWon: json['prize_title_won']?.toString(),
    );
  }
}
