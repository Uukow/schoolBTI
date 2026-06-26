import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:awesome_dialog/awesome_dialog.dart';

class SweetAlert {
  /// Show success dialog
  static void showSuccess({
    required BuildContext context,
    required String title,
    required String message,
    VoidCallback? onConfirm,
  }) {
    AwesomeDialog(
      context: context,
      dialogType: DialogType.success,
      animType: AnimType.bottomSlide,
      title: title,
      desc: message,
      titleTextStyle: GoogleFonts.montserrat(
        fontSize: 20,
        fontWeight: FontWeight.w600,
      ),
      descTextStyle: GoogleFonts.montserrat(fontSize: 14),
      btnOkText: 'OK',
      btnOkColor: Colors.green,
      btnOkOnPress: () {
        if (onConfirm != null) {
          onConfirm();
        }
      },
      dismissOnTouchOutside: true,
      dismissOnBackKeyPress: true,
    ).show();
  }

  /// Show error dialog
  static void showError({
    required BuildContext context,
    required String title,
    required String message,
    VoidCallback? onConfirm,
  }) {
    AwesomeDialog(
      context: context,
      dialogType: DialogType.error,
      animType: AnimType.scale,
      title: title,
      desc: message,
      titleTextStyle: GoogleFonts.montserrat(
        fontSize: 20,
        fontWeight: FontWeight.w600,
      ),
      descTextStyle: GoogleFonts.montserrat(fontSize: 14),
      btnOkText: 'OK',
      btnOkColor: Colors.red,
      btnOkOnPress: () {
        if (onConfirm != null) {
          onConfirm();
        }
      },
      dismissOnTouchOutside: true,
      dismissOnBackKeyPress: true,
    ).show();
  }

  /// Show warning dialog
  static void showWarning({
    required BuildContext context,
    required String title,
    required String message,
    VoidCallback? onConfirm,
  }) {
    AwesomeDialog(
      context: context,
      dialogType: DialogType.warning,
      animType: AnimType.topSlide,
      title: title,
      desc: message,
      titleTextStyle: GoogleFonts.montserrat(
        fontSize: 20,
        fontWeight: FontWeight.w600,
      ),
      descTextStyle: GoogleFonts.montserrat(fontSize: 14),
      btnOkText: 'OK',
      btnOkColor: Colors.orange,
      btnOkOnPress: onConfirm ?? () {},
    ).show();
  }

  /// Show info dialog
  static void showInfo({
    required BuildContext context,
    required String title,
    required String message,
    VoidCallback? onConfirm,
  }) {
    AwesomeDialog(
      context: context,
      dialogType: DialogType.info,
      animType: AnimType.scale,
      title: title,
      desc: message,
      titleTextStyle: GoogleFonts.montserrat(
        fontSize: 20,
        fontWeight: FontWeight.w600,
      ),
      descTextStyle: GoogleFonts.montserrat(fontSize: 14),
      btnOkText: 'OK',
      btnOkColor: Colors.blue,
      btnOkOnPress: onConfirm ?? () {},
    ).show();
  }

  /// Show confirmation dialog
  static void showConfirmation({
    required BuildContext context,
    required String title,
    required String message,
    required VoidCallback onConfirm,
    VoidCallback? onCancel,
    String confirmText = 'Confirm',
    String cancelText = 'Cancel',
    Color confirmColor = const Color(0xFF6D28D9),
  }) {
    AwesomeDialog(
      context: context,
      dialogType: DialogType.question,
      animType: AnimType.scale,
      title: title,
      desc: message,
      titleTextStyle: GoogleFonts.montserrat(
        fontSize: 20,
        fontWeight: FontWeight.w600,
      ),
      descTextStyle: GoogleFonts.montserrat(fontSize: 14),
      btnCancelText: cancelText,
      btnCancelOnPress: onCancel ?? () {},
      btnOkText: confirmText,
      btnOkColor: confirmColor,
      btnOkOnPress: onConfirm,
    ).show();
  }

  /// Show custom dialog with input field
  static void showInputDialog({
    required BuildContext context,
    required String title,
    required String message,
    required TextEditingController controller,
    required VoidCallback onConfirm,
    VoidCallback? onCancel,
    String confirmText = 'Submit',
    String cancelText = 'Cancel',
    String? hint,
    int maxLines = 1,
    DialogType dialogType = DialogType.info,
  }) {
    AwesomeDialog(
      context: context,
      dialogType: dialogType,
      animType: AnimType.scale,
      title: title,
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            if (message.isNotEmpty)
              Text(
                message,
                style: GoogleFonts.montserrat(fontSize: 14),
                textAlign: TextAlign.center,
              ),
            const SizedBox(height: 16),
            TextField(
              controller: controller,
              decoration: InputDecoration(
                hintText: hint,
                hintStyle: GoogleFonts.montserrat(),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                contentPadding: const EdgeInsets.all(12),
              ),
              maxLines: maxLines,
              style: GoogleFonts.montserrat(),
            ),
          ],
        ),
      ),
      btnCancelText: cancelText,
      btnCancelOnPress: onCancel ?? () {},
      btnOkText: confirmText,
      btnOkColor: const Color(0xFF6D28D9),
      btnOkOnPress: onConfirm,
    ).show();
  }

  /// Show loading dialog
  static void showLoading({
    required BuildContext context,
    String message = 'Please wait...',
  }) {
    AwesomeDialog(
      context: context,
      dialogType: DialogType.noHeader,
      animType: AnimType.scale,
      body: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF6D28D9)),
            ),
            const SizedBox(height: 16),
            Text(
              message,
              style: GoogleFonts.montserrat(fontSize: 14),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
      dismissOnTouchOutside: false,
      dismissOnBackKeyPress: false,
    ).show();
  }
}
