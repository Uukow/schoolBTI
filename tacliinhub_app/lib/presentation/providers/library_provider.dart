import 'package:flutter/foundation.dart';
import '../../data/repositories/library_repository.dart';
import '../../data/models/library_models.dart';

class LibraryProvider with ChangeNotifier {
  final LibraryRepository _repository = LibraryRepository();

  List<Book> _books = [];
  List<BookIssue> _issues = [];
  List<BookCategory> _categories = [];
  bool _isLoading = false;
  String? _error;

  List<Book> get books => _books;
  List<BookIssue> get issues => _issues;
  List<BookCategory> get categories => _categories;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // Get available books (not fully issued)
  List<Book> get availableBooks => _books.where((book) => book.availableCopies > 0 && book.status == 'Active').toList();

  // Get issued books
  List<BookIssue> get issuedBooks => _issues.where((issue) => issue.status == 'Issued' || issue.status == 'Overdue').toList();

  // Get overdue books
  List<BookIssue> get overdueBooks => _issues.where((issue) => issue.status == 'Overdue').toList();

  Future<void> loadBooks({
    String? search,
    String? category,
    String? status,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _books = await _repository.getBooks(
        search: search,
        category: category,
        status: status,
        userId: userId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _books = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadIssues({
    int? bookId,
    int? memberId,
    String? status,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _issues = await _repository.getBookIssues(
        bookId: bookId,
        memberId: memberId,
        status: status,
        userId: userId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _issues = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadCategories({int? userId}) async {
    try {
      _categories = await _repository.getCategories(userId: userId);
    } catch (e) {
      _error = e.toString();
      _categories = [];
    }
    notifyListeners();
  }

  Future<bool> addBook({
    required String isbn,
    required String title,
    String? author,
    String? publisher,
    String? edition,
    String? category,
    required int totalCopies,
    String? description,
    String? location,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addBook(
        isbn: isbn,
        title: title,
        author: author,
        publisher: publisher,
        edition: edition,
        category: category,
        totalCopies: totalCopies,
        description: description,
        location: location,
        userId: userId,
      );

      if (success) {
        await loadBooks(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> updateBook({
    required int bookId,
    String? isbn,
    String? title,
    String? author,
    String? publisher,
    String? edition,
    String? category,
    int? totalCopies,
    String? description,
    String? location,
    String? status,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.updateBook(
        bookId: bookId,
        isbn: isbn,
        title: title,
        author: author,
        publisher: publisher,
        edition: edition,
        category: category,
        totalCopies: totalCopies,
        description: description,
        location: location,
        status: status,
        userId: userId,
      );

      if (success) {
        await loadBooks(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> issueBook({
    required int bookId,
    required int memberId,
    required String memberType,
    required String issueDate,
    required String dueDate,
    String? remarks,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.issueBook(
        bookId: bookId,
        memberId: memberId,
        memberType: memberType,
        issueDate: issueDate,
        dueDate: dueDate,
        remarks: remarks,
        userId: userId,
      );

      if (success) {
        await loadIssues(userId: userId);
        await loadBooks(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> returnBook({
    required int issueId,
    required String returnDate,
    double? fineAmount,
    String? remarks,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.returnBook(
        issueId: issueId,
        returnDate: returnDate,
        fineAmount: fineAmount,
        remarks: remarks,
        userId: userId,
      );

      if (success) {
        await loadIssues(userId: userId);
        await loadBooks(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}

