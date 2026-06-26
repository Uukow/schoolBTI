import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/branch_models.dart';

class BranchRepository {
  /// Get all branches
  Future<List<Branch>> getAllBranches() async {
    try {
      print('📡 Fetching all branches...');
      
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/branches/index.php'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      print('📨 Response status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          print('✅ Successfully fetched branches');
          final List<dynamic> branchesJson = data['data'] ?? [];
          return branchesJson.map((json) => Branch.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load branches');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      print('❌ Error fetching branches: $e');
      throw Exception('Failed to load branches: ${e.toString()}');
    }
  }

  /// Get branch by ID
  Future<Branch> getBranchById(int branchId) async {
    try {
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/branches/index.php?id=$branchId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          return Branch.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Branch not found');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load branch: ${e.toString()}');
    }
  }

  /// Add new branch
  Future<bool> addBranch(Map<String, dynamic> branchData) async {
    try {
      print('📤 Adding new branch...');
      
      final response = await http.post(
        Uri.parse('${AppConstants.baseUrl}/branches/add.php'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode(branchData),
      ).timeout(const Duration(seconds: 15));

      print('📨 Response status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          print('✅ Branch added successfully');
          return true;
        } else {
          throw Exception(data['message'] ?? 'Failed to add branch');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      print('❌ Error adding branch: $e');
      throw Exception('Failed to add branch: ${e.toString()}');
    }
  }

  /// Update branch
  Future<bool> updateBranch(int branchId, Map<String, dynamic> branchData) async {
    try {
      final response = await http.put(
        Uri.parse('${AppConstants.baseUrl}/branches/update.php'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'id': branchId,
          ...branchData,
        }),
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          return true;
        } else {
          throw Exception(data['message'] ?? 'Failed to update branch');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to update branch: ${e.toString()}');
    }
  }

  /// Delete branch
  Future<bool> deleteBranch(int branchId) async {
    try {
      final response = await http.delete(
        Uri.parse('${AppConstants.baseUrl}/branches/delete.php?id=$branchId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          return true;
        } else {
          throw Exception(data['message'] ?? 'Failed to delete branch');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to delete branch: ${e.toString()}');
    }
  }
}














