import 'package:flutter/material.dart';
import '../../data/repositories/student_repository.dart';
import '../../data/models/student_models.dart';
import '../../data/models/class_models.dart';
import '../../core/branch_helper.dart';

class StudentProvider with ChangeNotifier {
  final StudentRepository _repository = StudentRepository();
  
  List<Student> _students = [];
  StudentStats? _stats;
  Student? _selectedStudent;
  List<SchoolClass> _classes = [];
  List<Section> _sections = [];
  bool _isLoading = false;
  String? _error;

  List<Student> get students => _students;
  StudentStats? get stats => _stats;
  Student? get selectedStudent => _selectedStudent;
  List<SchoolClass> get classes => _classes;
  List<Section> get sections => _sections;
  bool get isLoading => _isLoading;
  String? get error => _error;

  /// Load student statistics
  Future<void> loadStats() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _stats = await _repository.getStudentStats();
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load all students with filters
  Future<void> loadStudents({
    String? status,
    int? classId,
    int? sectionId,
    String? search,
    BuildContext? context, // Add context for branch filtering
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final branchId = BranchHelper.getBranchId(context);
      _students = await _repository.getAllStudents(
        status: status,
        classId: classId,
        sectionId: sectionId,
        search: search,
        branchId: branchId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load student by ID
  Future<void> loadStudentById(int studentId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _selectedStudent = await _repository.getStudentById(studentId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Add new student
  Future<bool> addStudent(Map<String, dynamic> studentData) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addStudent(studentData);
      if (success) {
        await loadStudents(); // Reload list
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

  /// Assign sections
  Future<bool> assignSections(List<Map<String, dynamic>> assignments) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.assignSections(assignments);
      if (success) {
        await loadStudents(); // Reload list
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Promote students
  Future<bool> promoteStudents(Map<String, dynamic> promotionData) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.promoteStudents(promotionData);
      if (success) {
        await loadStudents(); // Reload list
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

  /// Generate report
  Future<Map<String, dynamic>?> generateReport(String reportType, Map<String, dynamic> params) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final report = await _repository.generateReport(reportType, params);
      _isLoading = false;
      notifyListeners();
      return report;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return null;
    }
  }

  /// Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }

  /// Clear selected student
  void clearSelectedStudent() {
    _selectedStudent = null;
    notifyListeners();
  }

  /// Load all classes
  Future<void> loadClasses() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _classes = await _repository.getClasses();
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load sections for a class
  Future<void> loadSectionsByClass(int classId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _sections = await _repository.getSectionsByClass(classId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _sections = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Clear sections
  void clearSections() {
    _sections = [];
    notifyListeners();
  }
}











