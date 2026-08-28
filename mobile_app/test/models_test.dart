import 'package:flutter_test/flutter_test.dart';
import 'package:abvhpsapp/features/about/models/about_model.dart';
import 'package:abvhpsapp/features/team/models/team_member_model.dart';
import 'package:abvhpsapp/features/projects/models/project_model.dart';
import 'package:abvhpsapp/features/campaigns/models/campaign_model.dart';
import 'package:abvhpsapp/features/certificates/models/certificate_model.dart';
import 'package:abvhpsapp/features/blogs/models/blog_model.dart';
import 'package:abvhpsapp/features/gallery/models/gallery_model.dart';
import 'package:abvhpsapp/features/exams/models/exam_model.dart';
import 'package:abvhpsapp/features/wings/models/wing_model.dart';
import 'package:abvhpsapp/features/contact/models/contact_model.dart';
import 'package:abvhpsapp/features/verification/models/verification_model.dart';

void main() {
  group('Flutter Data Models JSON Serialization & Robustness', () {
    test('AboutData parses correctly with nested structures', () {
      final json = {
        'organization': {
          'name': 'AKHANDA BHARATHA VISWA HINDU PARIRAKSHANA SAMITI',
          'short_name': 'ABVHPS',
          'tagline': 'Preserving Sanathana Dharma',
          'founded_year': 2020,
          'registration_no': '72/2020',
          'founder_guru': 'Brahmasri Dr. Swamireddy',
          'logo_url': 'http://localhost/logo.png',
        },
        'mission': {
          'title': 'Our Mission',
          'paragraphs': ['Mission paragraph 1', 'Mission paragraph 2'],
        },
        'core_values': [
          {
            'id': '1',
            'title': 'Dharma',
            'description': 'Protecting dharma',
            'icon': 'temple',
          }
        ],
        'pillars': [
          {
            'title': 'Pillar 1',
            'description': 'Description 1',
          }
        ],
      };

      final data = AboutData.fromJson(json);
      expect(data.organization.name, 'AKHANDA BHARATHA VISWA HINDU PARIRAKSHANA SAMITI');
      expect(data.mission.paragraphs.length, 2);
      expect(data.coreValues.first.title, 'Dharma');
      expect(data.pillars.first.title, 'Pillar 1');
    });

    test('TeamMemberModel parses paginated data and filters', () {
      final json = {
        'data': [
          {
            'id': 1,
            'volunteer_id': 'VOL-1001',
            'full_name': 'Sri Rama',
            'cadre_label': 'National Convener',
            'jurisdiction_summary': 'All India',
            'photo_url': 'http://localhost/photo.jpg',
          }
        ],
        'meta': {
          'current_page': 1,
          'last_page': 2,
          'per_page': 20,
          'total': 25,
        },
        'filters': {
          'cadres': [
            {'value': 'national_convener', 'label': 'National Convener'}
          ],
          'districts': ['Guntur', 'Krishna'],
        }
      };

      final response = TeamPaginatedResponse.fromJson(json);
      expect(response.items.length, 1);
      expect(response.items.first.name, 'Sri Rama');
      expect(response.items.first.volunteerId, 'VOL-1001');
      expect(response.meta.hasNextPage, isTrue);
      expect(response.filters.districts.length, 2);
    });

    test('ProjectModel parses correctly', () {
      final json = {
        'id': 5,
        'name': 'Goshala Seva',
        'short_info': 'Caring for indigenous breeds',
        'image_url': 'http://localhost/goshala.jpg',
        'sort_order': 1,
      };

      final project = ProjectModel.fromJson(json);
      expect(project.id, 5);
      expect(project.name, 'Goshala Seva');
      expect(project.imageUrl, 'http://localhost/goshala.jpg');
    });

    test('CampaignModel parses correctly', () {
      final json = {
        'id': 10,
        'title': 'Temple Renovation Campaign',
        'description': 'Rebuilding historic shrine',
        'target_amount': 500000,
        'raised_amount': 250000,
        'target_formatted': '₹5,00,000',
        'raised_formatted': '₹2,50,000',
        'percent': 50.0,
        'whatsapp_share_url': 'https://api.whatsapp.com/send?text=Help',
      };

      final campaign = CampaignModel.fromJson(json);
      expect(campaign.id, 10);
      expect(campaign.targetAmount, 500000.0);
      expect(campaign.percent, 50.0);
      expect(campaign.raisedFormatted, '₹2,50,000');
    });

    test('CertificateModel parses correctly', () {
      final json = {
        'id': 1,
        'title': '80G Tax Exemption Certificate',
        'certificate_type': '80G',
        'document_number': 'CIT/80G/2023/101',
        'validity_summary': 'Perpetual',
        'download_url': 'http://localhost/80g.pdf',
      };

      final cert = CertificateModel.fromJson(json);
      expect(cert.certificateType, '80G');
      expect(cert.validitySummary, 'Perpetual');
      expect(cert.downloadUrl, 'http://localhost/80g.pdf');
    });

    test('BlogModel & Paginated parses correctly', () {
      final json = {
        'data': [
          {
            'id': 2,
            'title': 'Samiti Annual Sammelan 2026',
            'excerpt': 'Delegates gathered to discuss heritage conservation.',
            'content': '<p>Full content description.</p>',
            'published_at': '2026-08-20',
          }
        ],
        'meta': {
          'current_page': 1,
          'last_page': 1,
          'per_page': 12,
          'total': 1,
        }
      };

      final paginated = BlogPaginatedResponse.fromJson(json);
      expect(paginated.items.length, 1);
      expect(paginated.items.first.title, 'Samiti Annual Sammelan 2026');
      expect(paginated.meta.hasNextPage, isFalse);
    });

    test('GalleryModel parses correctly', () {
      final jsonImage = {
        'id': 1,
        'media_type': 'image',
        'image_url': 'http://localhost/img1.jpg',
      };
      final jsonVideo = {
        'id': 2,
        'media_type': 'video',
        'video_url': 'https://youtube.com/watch?v=123',
      };

      final img = GalleryModel.fromJson(jsonImage);
      final vid = GalleryModel.fromJson(jsonVideo);
      expect(img.isVideo, isFalse);
      expect(vid.isVideo, isTrue);
    });

    test('ExamModel, Winner, and Candidate Result parse correctly', () {
      final examJson = {
        'id': 3,
        'exam_title': 'Sanathana Dharma Pratibha Exam 2026',
        'application_fee': 100.0,
        'status': 'active',
        'prizes': ['1st Prize: Gold Medal', '2nd Prize: Silver Medal'],
      };
      final winnerJson = {
        'id': 1,
        'winner_rank': 1,
        'full_name': 'Candidate One',
        'exam_title': 'Pratibha Exam',
        'prize_title_won': 'Gold Medal',
      };
      final resultJson = {
        'full_name': 'Candidate One',
        'hall_ticket': '10000000001',
        'exam_title': 'Pratibha Exam',
        'marks_obtained': 95,
        'total_marks': 100,
        'percentage': 95.0,
        'grade': 'A+',
        'status': 'Passed',
      };

      final exam = ExamModel.fromJson(examJson);
      final winner = ExamWinnerModel.fromJson(winnerJson);
      final result = ExamResultModel.fromJson(resultJson);

      expect(exam.applicationFee, 100.0);
      expect(exam.prizes.length, 2);
      expect(winner.winnerRank, 1);
      expect(result.percentage, 95.0);
      expect(result.status, 'Passed');
    });

    test('WingModel & RudrasenaEligibility parse correctly', () {
      final wingJson = {
        'slug': 'rudrasena',
        'name': 'Rudra Sena',
        'slogan': 'Defenders of Dharma',
        'tagline': 'Dedicated youth force',
        'description': 'Youth wing for rapid assistance',
        'requires_age_check': true,
        'min_age': 24,
        'max_age': 44,
        'key_initiatives': ['Disaster Relief', 'Security Coordination'],
      };
      final eligibilityJson = {
        'success': true,
        'eligible': true,
        'age': 28,
        'message': 'Clearance verified for Rudra Sena duty.',
      };

      final wing = WingModel.fromJson(wingJson);
      final eligibility = RudrasenaEligibilityResult.fromJson(eligibilityJson);

      expect(wing.slug, 'rudrasena');
      expect(wing.requiresAgeCheck, isTrue);
      expect(eligibility.eligible, isTrue);
      expect(eligibility.age, 28);
    });

    test('ContactInfoModel parses correctly', () {
      final json = {
        'phone': '+91 8884933379',
        'email': 'info@abvhps.org',
        'address': 'D.No. 4-1-1, Main Road, Guntur',
        'whatsapp_number': '+91 9989980055',
        'social_links': [
          {
            'id': 'facebook',
            'name': 'Facebook',
            'short_name': 'FB',
            'url': 'https://facebook.com/abvhps',
          }
        ]
      };

      final contact = ContactInfoModel.fromJson(json);
      expect(contact.phone, '+91 8884933379');
      expect(contact.socialLinks.first.name, 'Facebook');
    });

    test('PublicVerificationResult parses correctly', () {
      final json = {
        'success': true,
        'is_valid': true,
        'entity_type': 'volunteer',
        'official_id_label': 'Volunteer ID',
        'official_id': 'VOL-1001',
        'name': 'Sri Rama',
        'cadre': 'State Executive Member',
        'location': 'Andhra Pradesh',
        'verified_since': '2023-01-15',
      };

      final result = PublicVerificationResult.fromJson(json);
      expect(result.isValid, isTrue);
      expect(result.name, 'Sri Rama');
      expect(result.officialId, 'VOL-1001');
    });
  });
}
