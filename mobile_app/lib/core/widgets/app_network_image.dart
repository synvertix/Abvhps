import 'package:flutter/material.dart';
import '../../core/theme/app_theme.dart';

class AppNetworkImage extends StatelessWidget {
  final String? imageUrl;
  final String? fallbackAsset;
  final double? width;
  final double? height;
  final BoxFit fit;
  final BorderRadius? borderRadius;
  final Widget? placeholder;
  final Widget? errorWidget;

  const AppNetworkImage({
    super.key,
    required this.imageUrl,
    this.fallbackAsset,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
    this.borderRadius,
    this.placeholder,
    this.errorWidget,
  });

  @override
  Widget build(BuildContext context) {
    Widget image;

    if (imageUrl != null && imageUrl!.isNotEmpty) {
      image = Image.network(
        imageUrl!,
        width: width,
        height: height,
        fit: fit,
        loadingBuilder: (context, child, loadingProgress) {
          if (loadingProgress == null) return child;
          return placeholder ??
              Container(
                width: width,
                height: height,
                color: Colors.grey.shade200,
                child: const Center(
                  child: SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: AppTheme.primaryOrange,
                    ),
                  ),
                ),
              );
        },
        errorBuilder: (context, error, stackTrace) {
          if (fallbackAsset != null) {
            return Image.asset(
              fallbackAsset!,
              width: width,
              height: height,
              fit: fit,
              errorBuilder: (context, err, st) =>
                  errorWidget ??
                  Container(
                    width: width,
                    height: height,
                    color: Colors.grey.shade100,
                    child: Icon(
                      Icons.image_outlined,
                      color: Colors.grey.shade400,
                      size: 28,
                    ),
                  ),
            );
          }
          return errorWidget ??
              Container(
                width: width,
                height: height,
                color: Colors.grey.shade100,
                child: Icon(
                  Icons.broken_image_outlined,
                  color: Colors.grey.shade400,
                  size: 28,
                ),
              );
        },
      );
    } else if (fallbackAsset != null) {
      image = Image.asset(
        fallbackAsset!,
        width: width,
        height: height,
        fit: fit,
        errorBuilder: (context, err, st) =>
            errorWidget ??
            Container(
              width: width,
              height: height,
              color: Colors.grey.shade100,
              child: Icon(
                Icons.image_outlined,
                color: Colors.grey.shade400,
                size: 28,
              ),
            ),
      );
    } else {
      image = errorWidget ??
          Container(
            width: width,
            height: height,
            color: Colors.grey.shade100,
            child: Icon(
              Icons.image_outlined,
              color: Colors.grey.shade400,
              size: 28,
            ),
          );
    }

    if (borderRadius != null) {
      return ClipRRect(
        borderRadius: borderRadius!,
        child: image,
      );
    }

    return image;
  }
}
