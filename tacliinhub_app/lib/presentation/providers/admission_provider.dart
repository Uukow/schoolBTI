import 'package:flutter/material.dart';
import '../../data/repositories/admission_repository.dart';
import '../../data/models/admission_models.dart';

class AdmissionProvider with ChangeNotifier {
  final AdmissionRepository _repository = AdmissionRepository();
  
  List<Admission> _admissions = [];
  AdmissionStats? _stats;
  Admission? _selectedAdmission;
  bool _isLoading = false;
  String? _error;

  List<Admission> get admissions => _admissions;
  AdmissionStats? get stats => _stats;
  Admission? get selectedAdmission => _selectedAdmission;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // Filter admissions by status
  List<Admission> get pendingAdmissions => 
      _admissions.where((a) => a.status.toLowerCase() == 'pending').toList();
  
  List<Admission> get approvedAdmissions => 
      _admissions.where((a) => a.status.toLowerCase() == 'approved').toList();

  /// Load admission statistics
  Future<void> loadStats() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _stats = await _repository.getAdmissionStats();
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load all admissions with filters
  Future<void> loadAdmissions({
    String? status,
    int? classId,
    int? branchId,
    String? search,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _admissions = await _repository.getAllAdmissions(
        status: status,
        classId: classId,
        branchId: branchId,
        search: search,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load admission by ID
  Future<void> loadAdmissionById(int admissionId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _selectedAdmission = await _repository.getAdmissionById(admissionId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Approve admission
  Future<bool> approveAdmission(int admissionId, String? remarks) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.approveAdmission(admissionId, remarks);
      if (success) {
        await loadAdmissions(); // Reload list
        await loadStats(); // Reload stats
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Reject admission
  Future<bool> rejectAdmission(int admissionId, String? remarks) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.rejectAdmission(admissionId, remarks);
      if (success) {
        await loadAdmissions(); // Reload list
        await loadStats(); // Reload stats
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

  /// Clear selected admission
  void clearSelectedAdmission() {
    _selectedAdmission = null;
    notifyListeners();
  }
}














