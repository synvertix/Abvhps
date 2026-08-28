import '../../../core/models/pagination_meta.dart';

class TeamPaginatedResponse {
  final List<TeamMemberModel> items;
  final PaginationMeta meta;
  final TeamFilters filters;

  const TeamPaginatedResponse({
    required this.items,
    required this.meta,
    required this.filters,
  });

  factory TeamPaginatedResponse.fromJson(Map<String, dynamic> json) {
    final list = (json['data'] as List<dynamic>? ?? [])
        .map((e) => TeamMemberModel.fromJson(e as Map<String, dynamic>))
        .toList();
    final meta = PaginationMeta.fromJson(json['meta'] as Map<String, dynamic>?);
    final filters = TeamFilters.fromJson(json['filters'] as Map<String, dynamic>? ?? {});

    return TeamPaginatedResponse(
      items: list,
      meta: meta,
      filters: filters,
    );
  }
}

class TeamMemberModel {
  final int id;
  final String volunteerId;
  final String name;
  final String cadreLabel;
  final String? jurisdictionSummary;
  final String? country;
  final String? state;
  final String? district;
  final String? assemblySegment;
  final String? mandal;
  final String? gramaPanchayat;
  final String? photoUrl;

  const TeamMemberModel({
    required this.id,
    required this.volunteerId,
    required this.name,
    required this.cadreLabel,
    this.jurisdictionSummary,
    this.country,
    this.state,
    this.district,
    this.assemblySegment,
    this.mandal,
    this.gramaPanchayat,
    this.photoUrl,
  });

  factory TeamMemberModel.fromJson(Map<String, dynamic> json) {
    return TeamMemberModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      volunteerId: json['volunteer_id']?.toString() ?? '',
      name: (json['name'] ?? json['full_name'])?.toString() ?? 'Volunteer',
      cadreLabel: json['cadre_label']?.toString() ?? 'Field Volunteer',
      jurisdictionSummary: json['jurisdiction_summary']?.toString(),
      country: json['country']?.toString(),
      state: json['state']?.toString(),
      district: json['district']?.toString(),
      assemblySegment: json['assembly_segment']?.toString(),
      mandal: json['mandal']?.toString(),
      gramaPanchayat: json['grama_panchayat']?.toString(),
      photoUrl: json['photo_url']?.toString(),
    );
  }
}

class TeamFilters {
  final List<String> cadres;
  final List<String> countries;
  final List<String> states;
  final List<String> districts;
  final List<String> assemblies;
  final List<String> mandals;
  final List<String> panchayats;

  const TeamFilters({
    this.cadres = const [],
    this.countries = const [],
    this.states = const [],
    this.districts = const [],
    this.assemblies = const [],
    this.mandals = const [],
    this.panchayats = const [],
  });

  factory TeamFilters.fromJson(Map<String, dynamic> json) {
    List<String> parseList(dynamic raw) {
      if (raw is List) {
        return raw.map((e) => e.toString()).where((e) => e.isNotEmpty).toList();
      }
      return [];
    }

    return TeamFilters(
      cadres: parseList(json['cadres']),
      countries: parseList(json['countries']),
      states: parseList(json['states']),
      districts: parseList(json['districts']),
      assemblies: parseList(json['assemblies']),
      mandals: parseList(json['mandals']),
      panchayats: parseList(json['panchayats']),
    );
  }
}
