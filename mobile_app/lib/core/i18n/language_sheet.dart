import 'package:flutter/material.dart';

import '../theme/app_theme.dart';
import 'i18n.dart';

/// Bottom sheet that lets the user pick the app language (11 languages).
Future<void> showLanguageSheet(BuildContext context) {
  final i18n = I18nScope.maybeOf(context);
  if (i18n == null) return Future.value();

  return showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.white,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
    ),
    builder: (sheetContext) => SafeArea(
      child: ConstrainedBox(
        constraints: BoxConstraints(maxHeight: MediaQuery.of(sheetContext).size.height * 0.75),
        child: ListenableBuilder(
          listenable: i18n,
          builder: (context, _) => Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 10),
              Container(
                width: 42,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                child: Row(
                  children: [
                    const Icon(Icons.language, color: AppTheme.templeGold),
                    const SizedBox(width: 10),
                    Text(
                      i18n.t('Choose language'),
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.neutralGray,
                      ),
                    ),
                  ],
                ),
              ),
              const Divider(height: 1),
              Flexible(
                child: ListView(
                  shrinkWrap: true,
                  children: [
                    for (final lang in kAppLanguages)
                      ListTile(
                        title: Text(
                          lang.native,
                          style: TextStyle(
                            fontWeight: lang.code == i18n.code ? FontWeight.w800 : FontWeight.w600,
                            color: lang.code == i18n.code ? AppTheme.primaryOrange : AppTheme.textPrimary,
                          ),
                        ),
                        trailing: lang.code == i18n.code
                            ? const Icon(Icons.check_circle, color: AppTheme.primaryOrange)
                            : null,
                        onTap: () async {
                          await i18n.setLanguage(lang.code);
                          if (sheetContext.mounted) Navigator.of(sheetContext).pop();
                        },
                      ),
                  ],
                ),
              ),
              const SizedBox(height: 8),
            ],
          ),
        ),
      ),
    ),
  );
}
