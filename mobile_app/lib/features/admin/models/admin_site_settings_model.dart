class AdminSiteSettings {
  final String siteTitle;
  final String contactPhone;
  final String whatsappNumber;
  final String contactEmail;
  final String contactAddress;
  final String facebookUrl;
  final String twitterUrl;
  final String youtubeUrl;
  final String footerAbout;
  final String membershipFee;
  final String volunteerFee;
  final String homepageJoinEnabled;
  final String homepageJoinWhyHeading;
  final String homepageJoinWhyText;
  final String homepageJoinMemberHeading;
  final String homepageJoinMemberText;
  final String homepageJoinCtaText;
  final String homepageSponsorsEnabled;
  final String homepageSponsorsHeading;
  final String homepageSocialEnabled;
  final String homepageSocialHeading;
  final String homepageSocialSubtext;

  const AdminSiteSettings({
    required this.siteTitle,
    required this.contactPhone,
    required this.whatsappNumber,
    required this.contactEmail,
    required this.contactAddress,
    required this.facebookUrl,
    required this.twitterUrl,
    required this.youtubeUrl,
    required this.footerAbout,
    required this.membershipFee,
    required this.volunteerFee,
    required this.homepageJoinEnabled,
    required this.homepageJoinWhyHeading,
    required this.homepageJoinWhyText,
    required this.homepageJoinMemberHeading,
    required this.homepageJoinMemberText,
    required this.homepageJoinCtaText,
    required this.homepageSponsorsEnabled,
    required this.homepageSponsorsHeading,
    required this.homepageSocialEnabled,
    required this.homepageSocialHeading,
    required this.homepageSocialSubtext,
  });

  factory AdminSiteSettings.fromJson(Map<String, dynamic> json) {
    return AdminSiteSettings(
      siteTitle: json['site_title'] as String? ?? 'ABVHPS',
      contactPhone: json['contact_phone'] as String? ?? '+91 9989980055',
      whatsappNumber: json['whatsapp_number'] as String? ?? '919989980055',
      contactEmail: json['contact_email'] as String? ?? 'info@abvhps.org',
      contactAddress: json['contact_address'] as String? ?? '',
      facebookUrl: json['facebook_url'] as String? ?? '',
      twitterUrl: json['twitter_url'] as String? ?? '',
      youtubeUrl: json['youtube_url'] as String? ?? '',
      footerAbout: json['footer_about'] as String? ?? '',
      membershipFee: json['membership_fee'] as String? ?? '100.00',
      volunteerFee: json['volunteer_fee'] as String? ?? '150.00',
      homepageJoinEnabled: json['homepage_join_enabled'] as String? ?? '1',
      homepageJoinWhyHeading: json['homepage_join_why_heading'] as String? ?? '',
      homepageJoinWhyText: json['homepage_join_why_text'] as String? ?? '',
      homepageJoinMemberHeading: json['homepage_join_member_heading'] as String? ?? '',
      homepageJoinMemberText: json['homepage_join_member_text'] as String? ?? '',
      homepageJoinCtaText: json['homepage_join_cta_text'] as String? ?? 'BECOME A MEMBER',
      homepageSponsorsEnabled: json['homepage_sponsors_enabled'] as String? ?? '1',
      homepageSponsorsHeading: json['homepage_sponsors_heading'] as String? ?? '',
      homepageSocialEnabled: json['homepage_social_enabled'] as String? ?? '1',
      homepageSocialHeading: json['homepage_social_heading'] as String? ?? '',
      homepageSocialSubtext: json['homepage_social_subtext'] as String? ?? '',
    );
  }
}
