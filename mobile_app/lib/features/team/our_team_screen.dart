import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/team_member_model.dart';

class TeamQueryState {
  final int page;
  final String search;
  final String? selectedCadre;
  final String? selectedDistrict;
  final List<TeamMemberModel> members;
  final TeamFilters filters;
  final bool hasNextPage;
  final bool isLoadingMore;

  const TeamQueryState({
    this.page = 1,
    this.search = '',
    this.selectedCadre,
    this.selectedDistrict,
    this.members = const [],
    this.filters = const TeamFilters(),
    this.hasNextPage = false,
    this.isLoadingMore = false,
  });

  TeamQueryState copyWith({
    int? page,
    String? search,
    String? selectedCadre,
    String? selectedDistrict,
    List<TeamMemberModel>? members,
    TeamFilters? filters,
    bool? hasNextPage,
    bool? isLoadingMore,
    bool clearCadre = false,
    bool clearDistrict = false,
  }) {
    return TeamQueryState(
      page: page ?? this.page,
      search: search ?? this.search,
      selectedCadre: clearCadre ? null : (selectedCadre ?? this.selectedCadre),
      selectedDistrict: clearDistrict ? null : (selectedDistrict ?? this.selectedDistrict),
      members: members ?? this.members,
      filters: filters ?? this.filters,
      hasNextPage: hasNextPage ?? this.hasNextPage,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
    );
  }
}

final teamNotifierProvider = StateNotifierProvider.autoDispose<TeamNotifier, AsyncValue<TeamQueryState>>((ref) {
  final repo = ref.watch(publicApiRepositoryProvider);
  return TeamNotifier(repo);
});

class TeamNotifier extends StateNotifier<AsyncValue<TeamQueryState>> {
  final PublicApiRepository _repo;

  TeamNotifier(this._repo) : super(const AsyncValue.loading()) {
    loadInitial();
  }

  Future<void> loadInitial() async {
    state = const AsyncValue.loading();
    try {
      final res = await _repo.getTeam(page: 1, perPage: 20);
      state = AsyncValue.data(TeamQueryState(
        page: 1,
        members: res.items,
        filters: res.filters,
        hasNextPage: res.meta.hasNextPage,
      ));
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> applyFilter({
    String? search,
    String? cadre,
    String? district,
    bool clearCadre = false,
    bool clearDistrict = false,
  }) async {
    final current = state.valueOrNull ?? const TeamQueryState();
    final newSearch = search ?? current.search;
    final newCadre = clearCadre ? null : (cadre ?? current.selectedCadre);
    final newDistrict = clearDistrict ? null : (district ?? current.selectedDistrict);

    state = const AsyncValue.loading();
    try {
      final res = await _repo.getTeam(
        page: 1,
        perPage: 20,
        search: newSearch.isNotEmpty ? newSearch : null,
        cadre: newCadre,
        district: newDistrict,
      );
      state = AsyncValue.data(TeamQueryState(
        page: 1,
        search: newSearch,
        selectedCadre: newCadre,
        selectedDistrict: newDistrict,
        members: res.items,
        filters: res.filters,
        hasNextPage: res.meta.hasNextPage,
      ));
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> loadNextPage() async {
    final current = state.valueOrNull;
    if (current == null || !current.hasNextPage || current.isLoadingMore) return;

    state = AsyncValue.data(current.copyWith(isLoadingMore: true));
    try {
      final nextPage = current.page + 1;
      final res = await _repo.getTeam(
        page: nextPage,
        perPage: 20,
        search: current.search.isNotEmpty ? current.search : null,
        cadre: current.selectedCadre,
        district: current.selectedDistrict,
      );
      state = AsyncValue.data(current.copyWith(
        page: nextPage,
        members: [...current.members, ...res.items],
        hasNextPage: res.meta.hasNextPage,
        isLoadingMore: false,
      ));
    } catch (_) {
      state = AsyncValue.data(current.copyWith(isLoadingMore: false));
    }
  }
}

class OurTeamScreen extends ConsumerStatefulWidget {
  const OurTeamScreen({super.key});

  @override
  ConsumerState<OurTeamScreen> createState() => _OurTeamScreenState();
}

class _OurTeamScreenState extends ConsumerState<OurTeamScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final teamState = ref.watch(teamNotifierProvider);

    return PublicScaffold(
      title: 'Our Team & Leadership',
      body: Column(
        children: [
          // Search & Filter Header Bar
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
            ),
            child: Column(
              children: [
                // Search field
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Search by name, ID, cadre or district...',
                    hintStyle: const TextStyle(fontSize: 12, color: Colors.grey),
                    prefixIcon: const Icon(Icons.search, color: AppTheme.primaryOrange, size: 20),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18),
                            onPressed: () {
                              _searchController.clear();
                              ref.read(teamNotifierProvider.notifier).applyFilter(search: '');
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: Colors.grey.shade100,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  onSubmitted: (val) {
                    ref.read(teamNotifierProvider.notifier).applyFilter(search: val.trim());
                  },
                ),
              ],
            ),
          ),

          Expanded(
            child: teamState.when(
              data: (data) => _buildList(data),
              loading: () => const AppLoadingState(message: 'Loading leadership directory...'),
              error: (err, _) => AppErrorState(
                message: err.toString(),
                onRetry: () => ref.read(teamNotifierProvider.notifier).loadInitial(),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildList(TeamQueryState data) {
    if (data.members.isEmpty) {
      return AppEmptyState(
        title: 'No Volunteers Found',
        subtitle: 'Try adjusting your search query or removing active filters.',
        icon: Icons.people_outline,
        onAction: () {
          _searchController.clear();
          ref.read(teamNotifierProvider.notifier).loadInitial();
        },
        actionLabel: 'Reset Directory',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(14),
      itemCount: data.members.length + (data.hasNextPage ? 1 : 0),
      itemBuilder: (context, index) {
        if (index == data.members.length) {
          return Padding(
            padding: const EdgeInsets.symmetric(vertical: 16),
            child: Center(
              child: data.isLoadingMore
                  ? const CircularProgressIndicator(color: AppTheme.primaryOrange, strokeWidth: 2)
                  : OutlinedButton(
                      onPressed: () => ref.read(teamNotifierProvider.notifier).loadNextPage(),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppTheme.primaryOrange,
                        side: const BorderSide(color: AppTheme.primaryOrange),
                      ),
                      child: const Text('Load More Members'),
                    ),
            ),
          );
        }

        final m = data.members[index];
        return _buildMemberCard(m);
      },
    );
  }

  Widget _buildMemberCard(TeamMemberModel m) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Photo
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              color: AppTheme.lightOrange,
              shape: BoxShape.circle,
              border: Border.all(color: AppTheme.primaryOrange, width: 2),
            ),
            child: ClipOval(
              child: AppNetworkImage(
                imageUrl: m.photoUrl,
                fallbackAsset: 'assets/branding/logo_abvhps.png',
                fit: BoxFit.cover,
              ),
            ),
          ),
          const SizedBox(width: 14),

          // Details
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  m.name,
                  style: const TextStyle(
                    color: AppTheme.darkNavy,
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppTheme.lightOrange,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    m.cadreLabel,
                    style: const TextStyle(
                      color: AppTheme.primaryOrange,
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                const SizedBox(height: 6),
                Row(
                  children: [
                    const Icon(Icons.badge_outlined, size: 13, color: Colors.grey),
                    const SizedBox(width: 4),
                    Text(
                      'ID: ${m.volunteerId}',
                      style: const TextStyle(color: Colors.black54, fontSize: 11, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
                if (m.jurisdictionSummary != null && m.jurisdictionSummary!.isNotEmpty) ...[
                  const SizedBox(height: 3),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.location_on_outlined, size: 13, color: Colors.grey),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          m.jurisdictionSummary!,
                          style: const TextStyle(color: Colors.black54, fontSize: 11),
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
