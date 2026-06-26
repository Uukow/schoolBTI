import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../providers/class_provider.dart';
import '../providers/branch_provider.dart';
import '../../core/sweet_alert.dart';

class AddAdmissionPage extends StatefulWidget {
  const AddAdmissionPage({super.key});

  @override
  State<AddAdmissionPage> createState() => _AddAdmissionPageState();
}

class _AddAdmissionPageState extends State<AddAdmissionPage> {
  final _formKey = GlobalKey<FormState>();

  // Student Information
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _middleNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _dobController = TextEditingController();
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  final _stateController = TextEditingController();

  // Guardian Information
  final _guardianNameController = TextEditingController();
  final _guardianPhoneController = TextEditingController();
  final _guardianEmailController = TextEditingController();

  // Other Information
  final _previousSchoolController = TextEditingController();
  final _applicationFeeController = TextEditingController();

  String _selectedGender = 'Male';
  int? _selectedClassId;
  int? _selectedBranchId;
  String _paymentStatus = 'Unpaid';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ClassProvider>().loadClasses();
      context.read<BranchProvider>().loadBranches();
    });
  }

  @override
  void dispose() {
    _firstNameController.dispose();
    _lastNameController.dispose();
    _middleNameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _dobController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _stateController.dispose();
    _guardianNameController.dispose();
    _guardianPhoneController.dispose();
    _guardianEmailController.dispose();
    _previousSchoolController.dispose();
    _applicationFeeController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'New Admission Application',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF6D28D9),
        elevation: 0,
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Personal Information Section
            _buildSectionHeader('Personal Information', Icons.person),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _firstNameController,
              label: 'First Name *',
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
              label: 'Last Name *',
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
              controller: _middleNameController,
              label: 'Middle Name (Optional)',
              icon: Icons.person_outline,
            ),
            const SizedBox(height: 16),
            _buildGenderSelector(),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _dobController,
              label: 'Date of Birth *',
              icon: Icons.calendar_today,
              readOnly: true,
              onTap: () => _selectDate(),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please select date of birth';
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
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _phoneController,
              label: 'Phone Number',
              icon: Icons.phone,
              keyboardType: TextInputType.phone,
            ),

            const SizedBox(height: 32),

            // Address Information
            _buildSectionHeader('Address Information', Icons.location_on),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _addressController,
              label: 'Address *',
              icon: Icons.home,
              maxLines: 2,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please enter address';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _cityController,
              label: 'City',
              icon: Icons.location_city,
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _stateController,
              label: 'State/Region',
              icon: Icons.map,
            ),

            const SizedBox(height: 32),

            // Academic Information
            _buildSectionHeader('Academic Information', Icons.school),
            const SizedBox(height: 16),
            Consumer<ClassProvider>(
              builder: (context, provider, child) {
                return _buildClassDropdown(provider.classes);
              },
            ),
            const SizedBox(height: 16),
            Consumer<BranchProvider>(
              builder: (context, provider, child) {
                return _buildBranchDropdown(provider.branches);
              },
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _previousSchoolController,
              label: 'Previous School (Optional)',
              icon: Icons.school_outlined,
            ),

            const SizedBox(height: 32),

            // Guardian Information
            _buildSectionHeader('Guardian Information', Icons.family_restroom),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _guardianNameController,
              label: 'Guardian Name *',
              icon: Icons.person,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please enter guardian name';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _guardianPhoneController,
              label: 'Guardian Phone *',
              icon: Icons.phone,
              keyboardType: TextInputType.phone,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please enter guardian phone';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _guardianEmailController,
              label: 'Guardian Email',
              icon: Icons.email,
              keyboardType: TextInputType.emailAddress,
            ),

            const SizedBox(height: 32),

            // Payment Information
            _buildSectionHeader('Payment Information', Icons.payment),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _applicationFeeController,
              label: 'Application Fee',
              icon: Icons.attach_money,
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 16),
            _buildPaymentStatusSelector(),

            const SizedBox(height: 32),

            // Submit Button
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton.icon(
                onPressed: _submitForm,
                icon: const Icon(Icons.check_circle),
                label: Text(
                  'Submit Application',
                  style: GoogleFonts.montserrat(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF6D28D9),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title, IconData icon) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: const Color(0xFF6D28D9).withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: const Color(0xFF6D28D9), size: 24),
        ),
        const SizedBox(width: 12),
        Text(
          title,
          style: GoogleFonts.montserrat(
            fontSize: 18,
            fontWeight: FontWeight.w600,
            color: const Color(0xFF6D28D9),
          ),
        ),
      ],
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
        labelStyle: GoogleFonts.montserrat(),
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
        filled: true,
        fillColor: Colors.white,
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
          'Gender *',
          style: GoogleFonts.montserrat(
            fontSize: 16,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey[300]!),
          ),
          child: Row(
            children: [
              Expanded(
                child: RadioListTile<String>(
                  title: Text('Male', style: GoogleFonts.montserrat()),
                  value: 'Male',
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
                  value: 'Female',
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
        ),
      ],
    );
  }

  Widget _buildPaymentStatusSelector() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Payment Status',
          style: GoogleFonts.montserrat(
            fontSize: 16,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey[300]!),
          ),
          child: Row(
            children: [
              Expanded(
                child: RadioListTile<String>(
                  title: Text(
                    'Paid',
                    style: GoogleFonts.montserrat(fontSize: 13),
                  ),
                  value: 'Paid',
                  groupValue: _paymentStatus,
                  onChanged: (value) {
                    setState(() {
                      _paymentStatus = value!;
                    });
                  },
                  activeColor: Colors.green,
                ),
              ),
              Expanded(
                child: RadioListTile<String>(
                  title: Text(
                    'Unpaid',
                    style: GoogleFonts.montserrat(fontSize: 13),
                  ),
                  value: 'Unpaid',
                  groupValue: _paymentStatus,
                  onChanged: (value) {
                    setState(() {
                      _paymentStatus = value!;
                    });
                  },
                  activeColor: Colors.red,
                ),
              ),
              Expanded(
                child: RadioListTile<String>(
                  title: Text(
                    'Partial',
                    style: GoogleFonts.montserrat(fontSize: 13),
                  ),
                  value: 'Partial',
                  groupValue: _paymentStatus,
                  onChanged: (value) {
                    setState(() {
                      _paymentStatus = value!;
                    });
                  },
                  activeColor: Colors.orange,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildClassDropdown(List classes) {
    return DropdownButtonFormField<int>(
      decoration: InputDecoration(
        labelText: 'Class Applied For *',
        labelStyle: GoogleFonts.montserrat(),
        prefixIcon: const Icon(Icons.class_, color: Color(0xFF6D28D9)),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        filled: true,
        fillColor: Colors.white,
      ),
      initialValue: _selectedClassId,
      items: classes.map((cls) {
        return DropdownMenuItem<int>(
          value: cls.id,
          child: Text(cls.className, style: GoogleFonts.montserrat()),
        );
      }).toList(),
      onChanged: (value) {
        setState(() {
          _selectedClassId = value;
        });
      },
      validator: (value) {
        if (value == null) {
          return 'Please select a class';
        }
        return null;
      },
      style: GoogleFonts.montserrat(color: Colors.black),
    );
  }

  Widget _buildBranchDropdown(List branches) {
    return DropdownButtonFormField<int>(
      decoration: InputDecoration(
        labelText: 'Branch *',
        labelStyle: GoogleFonts.montserrat(),
        prefixIcon: const Icon(Icons.business, color: Color(0xFF6D28D9)),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        filled: true,
        fillColor: Colors.white,
      ),
      initialValue: _selectedBranchId,
      items: branches.map((branch) {
        return DropdownMenuItem<int>(
          value: branch.id,
          child: Text(branch.branchName, style: GoogleFonts.montserrat()),
        );
      }).toList(),
      onChanged: (value) {
        setState(() {
          _selectedBranchId = value;
        });
      },
      validator: (value) {
        if (value == null) {
          return 'Please select a branch';
        }
        return null;
      },
      style: GoogleFonts.montserrat(color: Colors.black),
    );
  }

  Future<void> _selectDate() async {
    final date = await showDatePicker(
      context: context,
      initialDate: DateTime(2010),
      firstDate: DateTime(1990),
      lastDate: DateTime.now(),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(primary: Color(0xFF6D28D9)),
          ),
          child: child!,
        );
      },
    );
    if (date != null) {
      _dobController.text = date.toString().split(' ')[0];
    }
  }

  void _submitForm() {
    if (_formKey.currentState!.validate()) {
      // Show confirmation
      SweetAlert.showConfirmation(
        context: context,
        title: 'Submit Application',
        message: 'Are you sure you want to submit this admission application?',
        confirmText: 'Submit',
        onConfirm: () {
          _processSubmission();
        },
      );
    }
  }

  void _processSubmission() {
    // Show loading
    SweetAlert.showLoading(
      context: context,
      message: 'Submitting application...',
    );

    // Simulate API call
    Future.delayed(const Duration(seconds: 2), () {
      // Close loading
      Navigator.of(context).pop();

      // Show success
      SweetAlert.showSuccess(
        context: context,
        title: 'Success!',
        message: 'Application submitted successfully!',
        onConfirm: () {
          Navigator.of(context).pop(); // Go back
        },
      );
    });

    // TODO: Implement actual API call
    // final admissionData = {
    //   'first_name': _firstNameController.text,
    //   'last_name': _lastNameController.text,
    //   'middle_name': _middleNameController.text,
    //   'gender': _selectedGender,
    //   'date_of_birth': _dobController.text,
    //   'email': _emailController.text,
    //   'phone': _phoneController.text,
    //   'address': _addressController.text,
    //   'city': _cityController.text,
    //   'state': _stateController.text,
    //   'class_applied_for': _selectedClassId,
    //   'branch_id': _selectedBranchId,
    //   'guardian_name': _guardianNameController.text,
    //   'guardian_phone': _guardianPhoneController.text,
    //   'guardian_email': _guardianEmailController.text,
    //   'previous_school': _previousSchoolController.text,
    //   'application_fee': _applicationFeeController.text,
    //   'payment_status': _paymentStatus,
    // };
  }
}












