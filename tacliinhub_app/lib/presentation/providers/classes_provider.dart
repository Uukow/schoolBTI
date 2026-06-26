import 'package:flutter/material.dart';
import '../../data/repositories/classes_repository.dart';

class ClassesProvider with ChangeNotifier {
  final ClassesRepository _repository = ClassesRepository();
  Map<String, dynamic>? _data;
  bool _isLoading = false;
  String? _error;

  Map<String, dynamic>? get data => _data;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> loadClasses(int userId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _data = await _repository.getClasses(userId);
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
