import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../presentation/providers/branch_filter_provider.dart';

/// Helper utility to get branch ID for API calls
/// Returns null for Super Admin viewing all branches, or specific branch ID
class BranchHelper {
  /// Get branch ID from BranchFilterProvider
  /// Returns null if viewing all branches (Super Admin) or specific branch ID
  static int? getBranchId(BuildContext? context) {
    if (context == null) return null;
    
    try {
      final branchProvider = Provider.of<BranchFilterProvider>(
        context,
        listen: false,
      );
      return branchProvider.getBranchIdForApi();
    } catch (e) {
      // Provider not available, return null
      return null;
    }
  }

  /// Get branch ID as query parameter string
  /// Returns empty string if null (all branches)
  static String getBranchIdParam(BuildContext? context) {
    final branchId = getBranchId(context);
    return branchId != null ? branchId.toString() : '';
  }

  /// Add branch_id to query parameters if available
  static Map<String, String> addBranchToParams(
    BuildContext? context,
    Map<String, String> params,
  ) {
    final branchId = getBranchId(context);
    if (branchId != null) {
      params['branch_id'] = branchId.toString();
    }
    return params;
  }
}

