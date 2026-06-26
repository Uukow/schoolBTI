import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants.dart';
import '../../core/branch_helper.dart';
import '../models/dashboard_models.dart';

class DashboardRepository {
  Future<DashboardData> getDashboardData(int userId, String role, {BuildContext? context}) async {
    final prefs = await SharedPreferences.getInstance();
    final cacheKey = 'dashboard_data_$userId';

    try {
      print('📡 Fetching dashboard data for user $userId...');
      final queryParams = <String, String>{'user_id': userId.toString()};
      
      // Add branch_id if available (for Super Admin filtering)
      final branchId = BranchHelper.getBranchId(context);
      if (branchId != null) {
        queryParams['branch_id'] = branchId.toString();
        print('🏢 Branch ID: $branchId');
      } else {
        print('🏢 Branch ID: null (All Branches)');
      }
      
      final url = '${AppConstants.baseUrl}/dashboard/index.php?${Uri(queryParameters: queryParams).query}';
      print('🌐 URL: $url');
      print('🔑 Role: $role');

      if (userId <= 0) {
        throw Exception('Invalid user ID: $userId');
      }

      final response = await http
          .get(
            Uri.parse(url),
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
          )
          .timeout(const Duration(seconds: 15));

      print('📨 Response status: ${response.statusCode}');
      final responseBody = response.body.trim();
      print(
        '📦 Response body preview: ${responseBody.substring(0, responseBody.length > 200 ? 200 : responseBody.length)}...',
      );

      // Check if response is HTML (error page)
      if (responseBody.startsWith('<') || responseBody.isEmpty) {
        throw Exception(
          'Server returned invalid response. Check server errors.',
        );
      }

      if (response.statusCode == 200) {
        try {
          final data = jsonDecode(responseBody);
          print(
            '📊 Response structure: success=${data['success']}, has_data=${data.containsKey('data')}',
          );

          if (data['success'] == true) {
            print('✅ Successfully fetched dashboard data');
            final dashboardData = data['data'] ?? {};
            print('📦 Dashboard data keys: ${dashboardData.keys.toList()}');

            // Cache the raw data
            await prefs.setString(cacheKey, jsonEncode(dashboardData));

            try {
              return DashboardData.fromJson(dashboardData, role);
            } catch (parseError) {
              print('❌ Error parsing dashboard data: $parseError');
              print(
                '📋 Data structure: ${jsonEncode(dashboardData).substring(0, 500)}...',
              );
              rethrow;
            }
          } else {
            throw Exception(data['message'] ?? 'Failed to load dashboard data');
          }
        } catch (e) {
          if (e.toString().contains('Exception')) {
            rethrow;
          }
          throw Exception('Invalid JSON response: ${e.toString()}');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      print('❌ Network error: $e');
      print('🔄 Attempting to load from cache...');

      // Try to load from cache on error
      final cachedString = prefs.getString(cacheKey);
      if (cachedString != null) {
        try {
          print('📂 Found cached data, parsing...');
          final cachedData = jsonDecode(cachedString);
          return DashboardData.fromJson(cachedData, role);
        } catch (cacheError) {
          print('❌ Cache parsing error: $cacheError');
          // Clear bad cache
          await prefs.remove(cacheKey);
          throw Exception(
            'Connection failed. Please check your network and try again.',
          );
        }
      }
      final errorMsg = e.toString();
      if (errorMsg.contains('Failed host lookup') ||
          errorMsg.contains('Connection refused')) {
        throw Exception(
          'Connection failed. Please check:\n1. XAMPP is running\n2. Your device is on the same network\n3. API URL is correct in constants.dart\n\nError: $errorMsg',
        );
      }
      throw Exception(
        'Connection failed. Please check your network and try again.\n\nError: $errorMsg',
      );
    }
  }

  /// Clear cached dashboard data
  Future<void> clearCache(int userId) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('dashboard_data_$userId');
    print('🗑️ Cleared cache for user $userId');
  }
}
