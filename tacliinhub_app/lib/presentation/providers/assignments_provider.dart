import 'package:flutter/material.dart';
import '../../data/repositories/assignments_repository.dart';

class AssignmentsProvider with ChangeNotifier {
  final AssignmentsRepository _repository = AssignmentsRepository();
  List<dynamic>? _assignments;
  bool _isLoading = false;
  String? _error;

  List<dynamic>? get assignments => _assignments;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> loadAssignments(int userId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _assignments = await _repository.getAssignments(userId);
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
