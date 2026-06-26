import 'package:flutter/material.dart';
import '../../data/repositories/class_repository.dart';
import '../../data/models/class_models.dart';

class ClassProvider with ChangeNotifier {
  final ClassRepository _repository = ClassRepository();
  
  List<SchoolClass> _classes = [];
  List<Section> _sections = [];
  bool _isLoading = false;
  String? _error;

  List<SchoolClass> get classes => _classes;
  List<Section> get sections => _sections;
  bool get isLoading => _isLoading;
  String? get error => _error;

  /// Load all classes
  Future<void> loadClasses() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _classes = await _repository.getAllClasses();
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load sections for a specific class
  Future<void> loadSectionsForClass(int classId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _sections = await _repository.getSectionsForClass(classId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
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

  /// Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }
}














