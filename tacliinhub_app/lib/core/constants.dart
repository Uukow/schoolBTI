import 'package:flutter/material.dart';

class AppConstants {
  // API URL - Production server
  // Production: https://tacliinhub.uukowtech.com/api
  // Local Development: http://10.0.2.2/bti/api (for Android Emulator)
  // Local Development: http://localhost/bti/api (for iOS Simulator)
  // Local Development: http://192.168.1.X/bti/api (for real device on same network)
  static const String baseUrl = 'https://tacliinhub.uukowtech.com/api';

  // Uncomment below for local development:
  // static const String baseUrl = 'http://10.0.2.2/bti/api'; // Android Emulator
  // static const String baseUrl = 'http://localhost/bti/api'; // iOS Simulator
  // static const String baseUrl = 'http://192.168.1.X/bti/api'; // Real device

  // Colors
  static const Color primaryColor = Color(0xFF6D28D9);
  static const Color secondaryColor = Color(0xFFFF9E02);
  static const Color scaffoldBackgroundColor = Color(0xFFF5F7FA);

  // Fonts
  static const String fontFamily = 'Montserrat';

  // Keys
  static const String tokenKey = 'auth_token';
  static const String userDataKey = 'user_data';
}
