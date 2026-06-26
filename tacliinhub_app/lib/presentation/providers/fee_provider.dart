import 'package:flutter/material.dart';
import '../../data/repositories/fee_repository.dart';
import '../../data/models/fee_models.dart';

class FeeProvider with ChangeNotifier {
  final FeeRepository _repository = FeeRepository();
  FeesSummary? _feesSummary;
  bool _isLoading = false;
  String? _error;

  FeesSummary? get feesSummary => _feesSummary;
  bool get isLoading => _isLoading;
  String? get error => _error;

  /// Load fees summary
  Future<void> loadFeesSummary(int userId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _feesSummary = await _repository.getFeesSummary(userId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Refresh fees summary
  Future<void> refreshFeesSummary(int userId) async {
    await loadFeesSummary(userId);
  }

  /// Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }
}














