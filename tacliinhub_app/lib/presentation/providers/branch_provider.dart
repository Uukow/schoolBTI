import 'package:flutter/material.dart';
import '../../data/repositories/branch_repository.dart';
import '../../data/models/branch_models.dart';

class BranchProvider with ChangeNotifier {
  final BranchRepository _repository = BranchRepository();
  List<Branch> _branches = [];
  Branch? _selectedBranch;
  bool _isLoading = false;
  String? _error;

  List<Branch> get branches => _branches;
  Branch? get selectedBranch => _selectedBranch;
  bool get isLoading => _isLoading;
  String? get error => _error;

  /// Load all branches
  Future<void> loadBranches() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _branches = await _repository.getAllBranches();
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load branch by ID
  Future<void> loadBranchById(int branchId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _selectedBranch = await _repository.getBranchById(branchId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Add new branch
  Future<bool> addBranch(Map<String, dynamic> branchData) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addBranch(branchData);
      if (success) {
        await loadBranches(); // Reload list
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Update branch
  Future<bool> updateBranch(int branchId, Map<String, dynamic> branchData) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.updateBranch(branchId, branchData);
      if (success) {
        await loadBranches(); // Reload list
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Delete branch
  Future<bool> deleteBranch(int branchId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.deleteBranch(branchId);
      if (success) {
        await loadBranches(); // Reload list
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }

  /// Clear selected branch
  void clearSelectedBranch() {
    _selectedBranch = null;
    notifyListeners();
  }
}














