import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/library_models.dart';

class LibraryRepository {
  // Library endpoints are in /ajax/library/, not /api/ajax/library/
  final String baseUrl = AppConstants.baseUrl.replaceAll('/api', '');

  Future<List<Book>> getBooks({
    String? search,
    String? category,
    String? status,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/library/get-books.php');
      final queryParams = <String, String>{};
      
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      if (category != null && category.isNotEmpty) queryParams['category'] = category;
      if (status != null && status.isNotEmpty) queryParams['status'] = status;

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> booksJson = data['data'] ?? [];
          return booksJson.map((json) => Book.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load books');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load books: $e');
    }
  }

  Future<Book> getBookById(int bookId, {int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/library/get-book.php');
      final queryParams = <String, String>{
        'book_id': bookId.toString(),
      };
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          return Book.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load book');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load book: $e');
    }
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
    try {
      final uri = Uri.parse('$baseUrl/ajax/library/add-book.php');
      final body = {
        'isbn': isbn,
        'title': title,
        'author': author,
        'publisher': publisher,
        'edition': edition,
        'category': category,
        'total_copies': totalCopies,
        'description': description,
        'location': location,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add book: $e');
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
    try {
      final uri = Uri.parse('$baseUrl/ajax/library/update-book.php');
      final body = {
        'book_id': bookId,
        if (isbn != null) 'isbn': isbn,
        if (title != null) 'title': title,
        if (author != null) 'author': author,
        if (publisher != null) 'publisher': publisher,
        if (edition != null) 'edition': edition,
        if (category != null) 'category': category,
        if (totalCopies != null) 'total_copies': totalCopies,
        if (description != null) 'description': description,
        if (location != null) 'location': location,
        if (status != null) 'status': status,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to update book: $e');
    }
  }

  Future<List<BookIssue>> getBookIssues({
    int? bookId,
    int? memberId,
    String? status,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/library/get-issues.php');
      final queryParams = <String, String>{};
      
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (bookId != null) queryParams['book_id'] = bookId.toString();
      if (memberId != null) queryParams['member_id'] = memberId.toString();
      if (status != null && status.isNotEmpty) queryParams['status'] = status;

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> issuesJson = data['data'] ?? [];
          return issuesJson.map((json) => BookIssue.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load book issues');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load book issues: $e');
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
    try {
      final uri = Uri.parse('$baseUrl/ajax/library/issue-book.php');
      final body = {
        'book_id': bookId,
        'member_id': memberId,
        'member_type': memberType,
        'issue_date': issueDate,
        'due_date': dueDate,
        if (remarks != null) 'remarks': remarks,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to issue book: $e');
    }
  }

  Future<bool> returnBook({
    required int issueId,
    required String returnDate,
    double? fineAmount,
    String? remarks,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/library/return-book.php');
      final body = {
        'issue_id': issueId,
        'return_date': returnDate,
        if (fineAmount != null) 'fine_amount': fineAmount,
        if (remarks != null) 'remarks': remarks,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to return book: $e');
    }
  }

  Future<List<BookCategory>> getCategories({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/library/get-categories.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> categoriesJson = data['data'] ?? [];
          return categoriesJson.map((json) => BookCategory.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load categories');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load categories: $e');
    }
  }
}

