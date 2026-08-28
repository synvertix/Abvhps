class ContactInfoModel {
  final String phone;
  final String email;
  final String address;
  final String? whatsappNumber;
  final String? whatsappUrl;
  final List<SocialLinkItem> socialLinks;

  const ContactInfoModel({
    required this.phone,
    required this.email,
    required this.address,
    this.whatsappNumber,
    this.whatsappUrl,
    this.socialLinks = const [],
  });

  factory ContactInfoModel.fromJson(Map<String, dynamic> json) {
    return ContactInfoModel(
      phone: json['phone']?.toString() ?? '+91 8884933379',
      email: json['email']?.toString() ?? 'info@abvhps.org',
      address: json['address']?.toString() ?? '',
      whatsappNumber: json['whatsapp_number']?.toString(),
      whatsappUrl: json['whatsapp_url']?.toString(),
      socialLinks: (json['social_links'] as List<dynamic>? ?? [])
          .map((e) => SocialLinkItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class SocialLinkItem {
  final String id;
  final String name;
  final String shortName;
  final String url;
  final String? ariaLabel;

  const SocialLinkItem({
    required this.id,
    required this.name,
    required this.shortName,
    required this.url,
    this.ariaLabel,
  });

  factory SocialLinkItem.fromJson(Map<String, dynamic> json) {
    return SocialLinkItem(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      shortName: json['short_name']?.toString() ?? '',
      url: json['url']?.toString() ?? '',
      ariaLabel: json['aria_label']?.toString(),
    );
  }
}
