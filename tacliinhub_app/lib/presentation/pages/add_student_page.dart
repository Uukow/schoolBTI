import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../providers/student_provider.dart';
import '../../core/sweet_alert.dart';

class AddStudentPage extends StatefulWidget {
  const AddStudentPage({super.key});

  @override
  State<AddStudentPage> createState() => _AddStudentPageState();
}

class _AddStudentPageState extends State<AddStudentPage> {
  final _formKey = GlobalKey<FormState>();
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _dobController = TextEditingController();
  final _addressController = TextEditingController();

  String _selectedGender = 'male';
  int? _selectedClassId;
  int? _selectedSectionId;

  @override
  void initState() {
    super.initState();
    // Load classes when page opens
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<StudentProvider>().loadClasses();
    });
  }

  @override
  void dispose() {
    _firstNameController.dispose();
    _lastNameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _dobController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Add New Student',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF6D28D9),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildSectionTitle('Personal Information'),
              const SizedBox(height: 16),
              _buildTextField(
                controller: _firstNameController,
                label: 'First Name',
                icon: Icons.person,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter first name';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              _buildTextField(
                controller: _lastNameController,
                label: 'Last Name',
                icon: Icons.person_outline,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter last name';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              _buildTextField(
                controller: _emailController,
                label: 'Email',
                icon: Icons.email,
                keyboardType: TextInputType.emailAddress,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter email';
                  }
                  if (!value.contains('@')) {
                    return 'Please enter valid email';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              _buildTextField(
                controller: _phoneController,
                label: 'Phone Number',
                icon: Icons.phone,
                keyboardType: TextInputType.phone,
              ),
              const SizedBox(height: 16),
              _buildTextField(
                controller: _dobController,
                label: 'Date of Birth',
                icon: Icons.calendar_today,
                readOnly: true,
                onTap: () async {
                  final date = await showDatePicker(
                    context: context,
                    initialDate: DateTime.now(),
                    firstDate: DateTime(2000),
                    lastDate: DateTime.now(),
                  );
                  if (date != null) {
                    _dobController.text = date.toString().split(' ')[0];
                  }
                },
              ),
              const SizedBox(height: 16),
              _buildGenderSelector(),
              const SizedBox(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _buildSectionTitle('Academic Information'),
                  IconButton(
                    icon: const Icon(Icons.refresh, color: Color(0xFF6D28D9)),
                    onPressed: () {
                      context.read<StudentProvider>().loadClasses();
                    },
                    tooltip: 'Refresh classes',
                  ),
                ],
              ),
              const SizedBox(height: 16),
              _buildClassDropdown(),
              const SizedBox(height: 16),
              _buildSectionDropdown(),
              const SizedBox(height: 24),
              _buildSectionTitle('Address'),
              const SizedBox(height: 16),
              _buildTextField(
                controller: _addressController,
                label: 'Address',
                icon: Icons.location_on,
                maxLines: 3,
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _submitForm,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF6D28D9),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: Text(
                    'Add Student',
                    style: GoogleFonts.montserrat(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: GoogleFonts.montserrat(
        fontSize: 18,
        fontWeight: FontWeight.w600,
        color: const Color(0xFF6D28D9),
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    String? Function(String?)? validator,
    TextInputType? keyboardType,
    int maxLines = 1,
    bool readOnly = false,
    VoidCallback? onTap,
  }) {
    return TextFormField(
      controller: controller,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: const Color(0xFF6D28D9)),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFF6D28D9), width: 2),
        ),
      ),
      validator: validator,
      keyboardType: keyboardType,
      maxLines: maxLines,
      readOnly: readOnly,
      onTap: onTap,
      style: GoogleFonts.montserrat(),
    );
  }

  Widget _buildGenderSelector() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Gender',
          style: GoogleFonts.montserrat(
            fontSize: 16,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            Expanded(
              child: RadioListTile<String>(
                title: Text('Male', style: GoogleFonts.montserrat()),
                value: 'male',
                groupValue: _selectedGender,
                onChanged: (value) {
                  setState(() {
                    _selectedGender = value!;
                  });
                },
                activeColor: const Color(0xFF6D28D9),
              ),
            ),
            Expanded(
              child: RadioListTile<String>(
                title: Text('Female', style: GoogleFonts.montserrat()),
                value: 'female',
                groupValue: _selectedGender,
                onChanged: (value) {
                  setState(() {
                    _selectedGender = value!;
                  });
                },
                activeColor: const Color(0xFF6D28D9),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildClassDropdown() {
    final provider = context.watch<StudentProvider>();
    final classes = provider.classes;
    final isLoading = provider.isLoading;

    // Show loading or error state
    if (isLoading && classes.isEmpty) {
      return DropdownButtonFormField<int>(
        decoration: InputDecoration(
          labelText: 'Class',
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: Colors.grey[300]!),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: Color(0xFF6D28D9), width: 2),
          ),
          prefixIcon: const Icon(Icons.class_, color: Color(0xFF6D28D9)),
          suffixIcon: const SizedBox(
            width: 20,
            height: 20,
            child: Padding(
              padding: EdgeInsets.all(12.0),
              child: CircularProgressIndicator(strokeWidth: 2),
            ),
          ),
        ),
        initialValue: null,
        items: const [],
        hint: Text('Loading classes...', style: TextStyle(color: Colors.grey)),
        onChanged: null,
        style: GoogleFonts.montserrat(color: Colors.black),
      );
    }

    // Show error if classes failed to load
    if (provider.error != null && classes.isEmpty) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          DropdownButtonFormField<int>(
            decoration: InputDecoration(
              labelText: 'Class',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.red[300]!),
              ),
              prefixIcon: const Icon(Icons.class_, color: Colors.red),
            ),
            initialValue: null,
            items: const [],
            hint: Text(
              'Failed to load classes',
              style: TextStyle(color: Colors.red),
            ),
            onChanged: null,
            style: GoogleFonts.montserrat(color: Colors.black),
          ),
          const SizedBox(height: 4),
          Text(
            provider.error ?? 'Error loading classes',
            style: GoogleFonts.montserrat(fontSize: 12, color: Colors.red),
          ),
        ],
      );
    }

    // If no classes available, show a message
    if (classes.isEmpty && !isLoading) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              border: Border.all(color: Colors.orange[300]!),
              borderRadius: BorderRadius.circular(12),
              color: Colors.orange[50],
            ),
            child: Row(
              children: [
                Icon(Icons.info_outline, color: Colors.orange[700]),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'No classes available. Please add classes in the admin panel first.',
                    style: GoogleFonts.montserrat(
                      fontSize: 14,
                      color: Colors.orange[900],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      );
    }

    return DropdownButtonFormField<int>(
      decoration: InputDecoration(
        labelText: 'Class',
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFF6D28D9), width: 2),
        ),
        prefixIcon: const Icon(Icons.class_, color: Color(0xFF6D28D9)),
      ),
      initialValue: _selectedClassId,
      hint: Text(
        'Select Class',
        style: GoogleFonts.montserrat(color: Colors.grey[600]),
      ),
      isExpanded: true,
      items: classes.map((classItem) {
        return DropdownMenuItem<int>(
          value: classItem.id,
          child: Text(classItem.className, style: GoogleFonts.montserrat()),
        );
      }).toList(),
      onChanged: (value) {
        if (value != null) {
          setState(() {
            _selectedClassId = value;
            _selectedSectionId = null; // Clear section when class changes
          });
          // Load sections for selected class
          provider.loadSectionsByClass(value);
        } else {
          setState(() {
            _selectedClassId = null;
            _selectedSectionId = null;
          });
          provider.clearSections();
        }
      },
      style: GoogleFonts.montserrat(color: Colors.black),
      validator: (value) {
        if (value == null) {
          return 'Please select a class';
        }
        return null;
      },
    );
  }

  Widget _buildSectionDropdown() {
    final provider = context.watch<StudentProvider>();
    final sections = provider.sections;
    final isLoading = provider.isLoading;

    // Show loading state when fetching sections
    if (isLoading && _selectedClassId != null && sections.isEmpty) {
      return DropdownButtonFormField<int>(
        decoration: InputDecoration(
          labelText: 'Section',
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: Colors.grey[300]!),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: Color(0xFF6D28D9), width: 2),
          ),
          prefixIcon: const Icon(Icons.group, color: Color(0xFF6D28D9)),
          suffixIcon: const SizedBox(
            width: 20,
            height: 20,
            child: Padding(
              padding: EdgeInsets.all(12.0),
              child: CircularProgressIndicator(strokeWidth: 2),
            ),
          ),
        ),
        initialValue: null,
        items: const [],
        hint: Text('Loading sections...', style: TextStyle(color: Colors.grey)),
        onChanged: null,
        style: GoogleFonts.montserrat(color: Colors.black),
      );
    }

    // Show message if no class selected
    if (_selectedClassId == null) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          border: Border.all(color: Colors.grey[300]!),
          borderRadius: BorderRadius.circular(12),
          color: Colors.grey[50],
        ),
        child: Row(
          children: [
            Icon(Icons.info_outline, color: Colors.grey[600]),
            const SizedBox(width: 8),
            Text(
              'Please select a class first',
              style: GoogleFonts.montserrat(
                fontSize: 14,
                color: Colors.grey[700],
              ),
            ),
          ],
        ),
      );
    }

    // Show message if no sections available
    if (sections.isEmpty && !isLoading) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          border: Border.all(color: Colors.orange[300]!),
          borderRadius: BorderRadius.circular(12),
          color: Colors.orange[50],
        ),
        child: Row(
          children: [
            Icon(Icons.info_outline, color: Colors.orange[700]),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                'No sections available for this class. Please add sections in the admin panel.',
                style: GoogleFonts.montserrat(
                  fontSize: 14,
                  color: Colors.orange[900],
                ),
              ),
            ),
          ],
        ),
      );
    }

    return DropdownButtonFormField<int>(
      decoration: InputDecoration(
        labelText: 'Section',
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFF6D28D9), width: 2),
        ),
        prefixIcon: const Icon(Icons.group, color: Color(0xFF6D28D9)),
      ),
      initialValue: _selectedSectionId,
      hint: Text(
        'Select Section',
        style: GoogleFonts.montserrat(color: Colors.grey[600]),
      ),
      isExpanded: true,
      items: sections.map((section) {
        return DropdownMenuItem<int>(
          value: section.id,
          child: Text(section.sectionName, style: GoogleFonts.montserrat()),
        );
      }).toList(),
      onChanged: (value) {
        setState(() {
          _selectedSectionId = value;
        });
      },
      style: GoogleFonts.montserrat(color: Colors.black),
      validator: (value) {
        if (_selectedClassId != null && value == null) {
          return 'Please select a section';
        }
        return null;
      },
    );
  }

  void _submitForm() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    // Show loading dialog
    if (mounted) {
      SweetAlert.showLoading(context: context, message: 'Adding student...');
    }

    try {
      final studentData = {
        'first_name': _firstNameController.text.trim(),
        'last_name': _lastNameController.text.trim(),
        'email': _emailController.text.trim(),
        'phone': _phoneController.text.trim(),
        'date_of_birth': _dobController.text.trim(),
        'gender': _selectedGender,
        'class_id': _selectedClassId,
        'section_id': _selectedSectionId,
        'address': _addressController.text.trim(),
      };

      final provider = context.read<StudentProvider>();
      final success = await provider.addStudent(studentData);

      // Dismiss loading dialog
      if (mounted) {
        Navigator.of(context, rootNavigator: true).pop();
      }

      if (success && mounted) {
        // Show success alert
        SweetAlert.showSuccess(
          context: context,
          title: 'Success!',
          message: 'Student added successfully!',
          onConfirm: () {
            Navigator.pop(context);
          },
        );
      } else if (mounted) {
        // Show error alert
        SweetAlert.showError(
          context: context,
          title: 'Failed',
          message: provider.error ?? 'Failed to add student. Please try again.',
        );
      }
    } catch (e) {
      // Dismiss loading dialog
      if (mounted) {
        Navigator.of(context, rootNavigator: true).pop();
      }

      // Show error alert
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'An error occurred: ${e.toString()}',
        );
      }
    }
  }
}












