import 'package:flutter/material.dart';
import '../../data/repositories/marks_repository.dart';

class MarksProvider with ChangeNotifier {
  final MarksRepository _repository = MarksRepository();
  List<dynamic>? _marks;
  bool _isLoading = false;
  String? _error;

  List<dynamic>? get marks => _marks;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> loadMarks(int userId, {int? subjectId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _marks = await _repository.getMarks(userId, subjectId: subjectId);
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
