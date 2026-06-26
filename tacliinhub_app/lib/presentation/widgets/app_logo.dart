import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/constants.dart';

/// App Logo Widget
///
/// A reusable widget for displaying the application logo with different variants
/// based on background theme (light/dark) and size requirements.
class AppLogo extends StatelessWidget {
  /// Logo variant type
  final LogoVariant variant;

  /// Logo size
  final double? height;

  /// Logo width (optional, maintains aspect ratio if not specified)
  final double? width;

  /// Whether to show text alongside logo
  final bool showText;

  /// Custom text to display (defaults to app name)
  final String? text;

  /// Text style
  final TextStyle? textStyle;

  /// Spacing between logo and text
  final double textSpacing;

  /// Alignment of logo
  final Alignment alignment;

  const AppLogo({
    super.key,
    this.variant = LogoVariant.dark,
    this.height,
    this.width,
    this.showText = false,
    this.text,
    this.textStyle,
    this.textSpacing = 12,
    this.alignment = Alignment.center,
  });

  /// Logo for light backgrounds (dark logo)
  const AppLogo.dark({
    super.key,
    double? height,
    double? width,
    bool showText = false,
    String? text,
    TextStyle? textStyle,
    double textSpacing = 12,
    Alignment alignment = Alignment.center,
  }) : variant = LogoVariant.dark,
       height = height,
       width = width,
       showText = showText,
       text = text,
       textStyle = textStyle,
       textSpacing = textSpacing,
       alignment = alignment;

  /// Logo for dark backgrounds (white logo)
  const AppLogo.light({
    super.key,
    double? height,
    double? width,
    bool showText = false,
    String? text,
    TextStyle? textStyle,
    double textSpacing = 12,
    Alignment alignment = Alignment.center,
  }) : variant = LogoVariant.light,
       height = height,
       width = width,
       showText = showText,
       text = text,
       textStyle = textStyle,
       textSpacing = textSpacing,
       alignment = alignment;

  /// Icon-only logo for light backgrounds
  const AppLogo.icon({
    super.key,
    double? height,
    double? width,
    bool showText = false,
    String? text,
    TextStyle? textStyle,
    double textSpacing = 12,
    Alignment alignment = Alignment.center,
  }) : variant = LogoVariant.icon,
       height = height,
       width = width,
       showText = showText,
       text = text,
       textStyle = textStyle,
       textSpacing = textSpacing,
       alignment = alignment;

  /// Icon-only logo for dark backgrounds
  const AppLogo.iconLight({
    super.key,
    double? height,
    double? width,
    bool showText = false,
    String? text,
    TextStyle? textStyle,
    double textSpacing = 12,
    Alignment alignment = Alignment.center,
  }) : variant = LogoVariant.iconLight,
       height = height,
       width = width,
       showText = showText,
       text = text,
       textStyle = textStyle,
       textSpacing = textSpacing,
       alignment = alignment;

  String _getAssetPath() {
    switch (variant) {
      case LogoVariant.dark:
        return 'assets/images/logo-dark.png';
      case LogoVariant.light:
        return 'assets/images/logo-white.png';
      case LogoVariant.icon:
        return 'assets/images/logo-icon.png';
      case LogoVariant.iconLight:
        return 'assets/images/logo-icon-white.png';
    }
  }

  @override
  Widget build(BuildContext context) {
    final logoImage = Image.asset(
      _getAssetPath(),
      height: height,
      width: width,
      fit: BoxFit.contain,
      alignment: alignment,
      errorBuilder: (context, error, stackTrace) {
        // Fallback to icon if image fails to load
        return Icon(
          Icons.school,
          size: height ?? 40,
          color: variant == LogoVariant.dark || variant == LogoVariant.icon
              ? AppConstants.primaryColor
              : Colors.white,
        );
      },
    );

    if (!showText) {
      return logoImage;
    }

    return LayoutBuilder(
      builder: (context, constraints) {
        // Calculate responsive font size
        final maxWidth = constraints.maxWidth;
        final fontSize = height != null
            ? (height! * 0.4).clamp(12.0, 24.0)
            : (maxWidth > 0 && maxWidth < 400 ? 16.0 : 20.0);

        return Row(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Flexible(flex: 2, child: logoImage),
            SizedBox(width: textSpacing),
            Flexible(
              flex: 3,
              child: Text(
                text ?? 'School ERP',
                style:
                    textStyle ??
                    GoogleFonts.montserrat(
                      fontSize: fontSize,
                      fontWeight: FontWeight.bold,
                      color:
                          variant == LogoVariant.dark ||
                              variant == LogoVariant.icon
                          ? Colors.grey[900]
                          : Colors.white,
                    ),
                overflow: TextOverflow.ellipsis,
                maxLines: 2,
                textAlign: TextAlign.left,
              ),
            ),
          ],
        );
      },
    );
  }
}

/// Logo variant types
enum LogoVariant {
  /// Dark logo for light backgrounds
  dark,

  /// White logo for dark backgrounds
  light,

  /// Icon logo for light backgrounds
  icon,

  /// Icon logo for dark backgrounds
  iconLight,
}

/// App Logo with automatic theme detection
class AppLogoAuto extends StatelessWidget {
  final double? height;
  final double? width;
  final bool showText;
  final String? text;
  final TextStyle? textStyle;
  final double textSpacing;
  final Alignment alignment;
  final bool useIcon;

  const AppLogoAuto({
    super.key,
    this.height,
    this.width,
    this.showText = false,
    this.text,
    this.textStyle,
    this.textSpacing = 12,
    this.alignment = Alignment.center,
    this.useIcon = false,
  });

  @override
  Widget build(BuildContext context) {
    final brightness = Theme.of(context).brightness;
    final isDark = brightness == Brightness.dark;

    if (useIcon) {
      return AppLogo(
        variant: isDark ? LogoVariant.iconLight : LogoVariant.icon,
        height: height,
        width: width,
        showText: showText,
        text: text,
        textStyle: textStyle,
        textSpacing: textSpacing,
        alignment: alignment,
      );
    }

    return AppLogo(
      variant: isDark ? LogoVariant.light : LogoVariant.dark,
      height: height,
      width: width,
      showText: showText,
      text: text,
      textStyle: textStyle,
      textSpacing: textSpacing,
      alignment: alignment,
    );
  }
}
