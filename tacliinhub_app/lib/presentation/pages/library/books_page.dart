import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/library_provider.dart';
import '../../providers/auth_provider.dart';

class BooksPage extends StatefulWidget {
  const BooksPage({super.key});

  @override
  State<BooksPage> createState() => _BooksPageState();
}

class _BooksPageState extends State<BooksPage> {
  final TextEditingController _searchController = TextEditingController();
  String? _selectedCategory;
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<LibraryProvider>();
      provider.loadBooks(userId: user?.id);
      // Load categories, but don't fail if it errors
      provider.loadCategories(userId: user?.id).catchError((e) {
        // Silently handle category loading errors
        print('Category loading error: $e');
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Books',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.blue,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => Navigator.pushNamed(context, '/library/books/add'),
            tooltip: 'Add Book',
          ),
        ],
      ),
      body: Column(
        children: [
          // Search and Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Search books...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear),
                            onPressed: () {
                              _searchController.clear();
                              _loadBooks();
                            },
                          )
                        : null,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  onChanged: (value) {
                    _loadBooks();
                  },
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: Consumer<LibraryProvider>(
                        builder: (context, provider, child) {
                          return DropdownButtonFormField<String>(
                            decoration: InputDecoration(
                              labelText: 'Category',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
                            ),
                            initialValue: _selectedCategory,
                            items: [
                              const DropdownMenuItem<String>(
                                value: null,
                                child: Text('All Categories'),
                              ),
                              ...provider.categories.map((cat) {
                                return DropdownMenuItem<String>(
                                  value: cat.name,
                                  child: Text(cat.name),
                                );
                              }),
                            ],
                            onChanged: (value) {
                              setState(() {
                                _selectedCategory = value;
                              });
                              _loadBooks();
                            },
                          );
                        },
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        decoration: InputDecoration(
                          labelText: 'Status',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                        ),
                        initialValue: _selectedStatus,
                        items: const [
                          DropdownMenuItem<String>(
                            value: null,
                            child: Text('All Status'),
                          ),
                          DropdownMenuItem(
                            value: 'Active',
                            child: Text('Active'),
                          ),
                          DropdownMenuItem(
                            value: 'Inactive',
                            child: Text('Inactive'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedStatus = value;
                          });
                          _loadBooks();
                        },
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Books List
          Expanded(
            child: Consumer<LibraryProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.error_outline,
                          size: 48,
                          color: Colors.red,
                        ),
                        const SizedBox(height: 16),
                        Text(provider.error ?? 'Error loading books'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadBooks,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.books.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.book_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No books found',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: provider.books.length,
                  itemBuilder: (context, index) {
                    final book = provider.books[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: Colors.blue.withOpacity(0.1),
                          child: const Icon(Icons.book, color: Colors.blue),
                        ),
                        title: Text(
                          book.title,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            if (book.author != null && book.author!.isNotEmpty)
                              Text('Author: ${book.author}'),
                            if (book.isbn.isNotEmpty)
                              Text('ISBN: ${book.isbn}'),
                            if (book.category != null)
                              Text('Category: ${book.category}'),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                Chip(
                                  label: Text('Total: ${book.totalCopies}'),
                                  backgroundColor: Colors.blue.withOpacity(0.1),
                                  labelStyle: const TextStyle(fontSize: 10),
                                ),
                                const SizedBox(width: 4),
                                Chip(
                                  label: Text(
                                    'Available: ${book.availableCopies}',
                                  ),
                                  backgroundColor: Colors.green.withOpacity(
                                    0.1,
                                  ),
                                  labelStyle: const TextStyle(fontSize: 10),
                                ),
                                const SizedBox(width: 4),
                                Chip(
                                  label: Text('Issued: ${book.issuedCopies}'),
                                  backgroundColor: Colors.orange.withOpacity(
                                    0.1,
                                  ),
                                  labelStyle: const TextStyle(fontSize: 10),
                                ),
                              ],
                            ),
                          ],
                        ),
                        trailing: book.availableCopies > 0
                            ? const Icon(
                                Icons.check_circle,
                                color: Colors.green,
                              )
                            : const Icon(Icons.cancel, color: Colors.red),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.pushNamed(context, '/library/books/add'),
        backgroundColor: Colors.blue,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _loadBooks() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    context.read<LibraryProvider>().loadBooks(
      search: _searchController.text.isEmpty ? null : _searchController.text,
      category: _selectedCategory,
      status: _selectedStatus,
      userId: user?.id,
    );
  }
}
