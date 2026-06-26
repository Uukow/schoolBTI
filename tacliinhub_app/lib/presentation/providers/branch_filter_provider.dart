import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../../data/models/branch_models.dart';
import '../../data/repositories/branch_repository.dart';
import 'auth_provider.dart';

/// Provider for managing branch filter selection
/// Super Admin can select any branch or view all branches
/// Other roles are restricted to their assigned branch
class BranchFilterProvider with ChangeNotifier {
  final BranchRepository _repository = BranchRepository();
  final FlutterSecureStorage _storage = const FlutterSecureStorage();
  
  List<Branch> _branches = [];
  int? _selectedBranchId; // null means "All Branches"
  bool _isLoading = false;
  String? _error;
  bool _isSuperAdmin = false;

  List<Branch> get branches => _branches;
  int? get selectedBranchId => _selectedBranchId;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isSuperAdmin => _isSuperAdmin;
  bool get showBranchSelector => _isSuperAdmin;

  /// Initialize branch filter based on user role
  Future<void> initialize(AuthProvider authProvider) async {
    _isLoading = true;
    notifyListeners();

    try {
      final user = authProvider.user;
      if (user == null) {
        _isLoading = false;
        notifyListeners();
        return;
      }

      // Check if user is Super Admin
      _isSuperAdmin = user.role == 'Super Admin';

      // Load branches if Super Admin
      if (_isSuperAdmin) {
        await loadBranches();
        
        // Load saved branch selection from storage
        await _loadSavedBranchSelection();
      } else {
        // Non-Super Admin: use their assigned branch
        _selectedBranchId = user.branchId;
        await _saveBranchSelection();
      }

      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load all branches
  Future<void> loadBranches() async {
    if (!_isSuperAdmin) return;

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

  /// Set selected branch (null = All Branches)
  Future<void> setSelectedBranch(int? branchId) async {
    if (!_isSuperAdmin) return;

    _selectedBranchId = branchId;
    await _saveBranchSelection();
    // Schedule notification for next frame to avoid layout conflicts during dropdown selection
    WidgetsBinding.instance.addPostFrameCallback((_) {
      notifyListeners();
    });
  }

  /// Get branch name for display
  String getSelectedBranchName() {
    if (_selectedBranchId == null) {
      return 'All Branches';
    }
    final branch = _branches.firstWhere(
      (b) => b.id == _selectedBranchId,
      orElse: () => Branch(
        id: _selectedBranchId!,
        branchName: 'Unknown Branch',
        branchCode: '',
        address: '',
        totalStudents: 0,
        totalStaff: 0,
        isActive: true,
        createdAt: '',
      ),
    );
    return branch.branchName;
  }

  /// Get branch ID for API calls (null for Super Admin viewing all, or specific ID)
  int? getBranchIdForApi() {
    if (!_isSuperAdmin) {
      // Non-Super Admin always use their assigned branch
      return _selectedBranchId;
    }
    // Super Admin: null means all branches, specific ID means filtered
    return _selectedBranchId;
  }

  /// Check if viewing all branches
  bool get isViewingAllBranches => _selectedBranchId == null;

  /// Save branch selection to secure storage
  Future<void> _saveBranchSelection() async {
    try {
      if (_selectedBranchId != null) {
        await _storage.write(
          key: 'selected_branch_id',
          value: _selectedBranchId.toString(),
        );
      } else {
        await _storage.delete(key: 'selected_branch_id');
      }
    } catch (e) {
      print('Error saving branch selection: $e');
    }
  }

  /// Load saved branch selection from secure storage
  Future<void> _loadSavedBranchSelection() async {
    try {
      final savedBranchId = await _storage.read(key: 'selected_branch_id');
      if (savedBranchId != null) {
        _selectedBranchId = int.tryParse(savedBranchId);
      } else {
        // Default to "All Branches" for Super Admin
        _selectedBranchId = null;
      }
    } catch (e) {
      print('Error loading branch selection: $e');
      _selectedBranchId = null;
    }
  }

  /// Clear branch selection (reset to All Branches)
  Future<void> clearSelection() async {
    if (!_isSuperAdmin) return;
    await setSelectedBranch(null);
  }

  /// Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }
}

