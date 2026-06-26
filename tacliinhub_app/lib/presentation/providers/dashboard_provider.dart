import 'package:flutter/material.dart';
import '../../data/repositories/dashboard_repository.dart';
import '../../data/models/dashboard_models.dart';

class DashboardProvider with ChangeNotifier {
  final DashboardRepository _repository = DashboardRepository();
  DashboardData? _data;
  bool _isLoading = false;
  String? _error;
  DateTime? _lastRefresh;

  DashboardData? get data => _data;
  bool get isLoading => _isLoading;
  String? get error => _error;
  DateTime? get lastRefresh => _lastRefresh;

  /// Load dashboard data from API
  Future<void> loadDashboardData(int userId, String role, {BuildContext? context}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _data = await _repository.getDashboardData(userId, role, context: context);
      _lastRefresh = DateTime.now();
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      // Keep existing data if available (offline mode)
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Refresh dashboard data (for pull-to-refresh)
  Future<void> refreshDashboardData(int userId, String role, {BuildContext? context}) async {
    try {
      await _repository.clearCache(userId);
      await loadDashboardData(userId, role, context: context);
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      notifyListeners();
    }
  }

  /// Clear error state
  void clearError() {
    _error = null;
    notifyListeners();
  }
}
