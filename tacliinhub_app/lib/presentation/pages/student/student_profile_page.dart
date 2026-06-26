import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';
import '../../../core/constants.dart';
import '../../../core/sweet_alert.dart';
import '../../providers/student_portal_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';

class StudentProfilePage extends StatefulWidget {
  const StudentProfilePage({super.key});

  @override
  State<StudentProfilePage> createState() => _StudentProfilePageState();
}

class _StudentProfilePageState extends State<StudentProfilePage> {
  final _formKey = GlobalKey<FormState>();
  bool _isEditing = false;
  bool _isSaving = false;
  
  // Controllers
  late TextEditingController _emailController;
  late TextEditingController _phoneController;
  late TextEditingController _addressController;
  late TextEditingController _cityController;
  late TextEditingController _stateController;
  late TextEditingController _postalCodeController;
  
  File? _selectedPhoto;
  final ImagePicker _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    _emailController = TextEditingController();
    _phoneController = TextEditingController();
    _addressController = TextEditingController();
    _cityController = TextEditingController();
    _stateController = TextEditingController();
    _postalCodeController = TextEditingController();
    
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        Provider.of<StudentPortalProvider>(context, listen: false)
            .loadProfile(userId: user.id);
      }
    });
  }

  @override
  void dispose() {
    _emailController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _stateController.dispose();
    _postalCodeController.dispose();
    super.dispose();
  }

  void _loadProfileData(profile) {
    if (profile != null) {
      _emailController.text = profile.email ?? '';
      _phoneController.text = profile.phone ?? '';
      _addressController.text = profile.address ?? '';
      _cityController.text = profile.city ?? '';
      _stateController.text = profile.state ?? '';
      _postalCodeController.text = profile.postalCode ?? '';
    }
  }

  Future<void> _pickImage() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 800,
        maxHeight: 800,
        imageQuality: 85,
      );
      
      if (image != null) {
        setState(() {
          _selectedPhoto = File(image.path);
        });
      }
    } catch (e) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to pick image: $e',
        );
      }
    }
  }

  Future<void> _takePhoto() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.camera,
        maxWidth: 800,
        maxHeight: 800,
        imageQuality: 85,
      );
      
      if (image != null) {
        setState(() {
          _selectedPhoto = File(image.path);
        });
      }
    } catch (e) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to take photo: $e',
        );
      }
    }
  }

  void _showImagePicker() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_library, color: AppConstants.primaryColor),
              title: Text('Choose from Gallery', style: GoogleFonts.montserrat()),
              onTap: () {
                Navigator.pop(context);
                _pickImage();
              },
            ),
            ListTile(
              leading: const Icon(Icons.camera_alt, color: AppConstants.primaryColor),
              title: Text('Take Photo', style: GoogleFonts.montserrat()),
              onTap: () {
                Navigator.pop(context);
                _takePhoto();
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isSaving = true;
    });

    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user == null) {
      setState(() {
        _isSaving = false;
      });
      return;
    }

    final provider = Provider.of<StudentPortalProvider>(context, listen: false);
    final success = await provider.updateProfile(
      userId: user.id,
      email: _emailController.text.trim().isEmpty ? null : _emailController.text.trim(),
      phone: _phoneController.text.trim().isEmpty ? null : _phoneController.text.trim(),
      address: _addressController.text.trim().isEmpty ? null : _addressController.text.trim(),
      city: _cityController.text.trim().isEmpty ? null : _cityController.text.trim(),
      state: _stateController.text.trim().isEmpty ? null : _stateController.text.trim(),
      postalCode: _postalCodeController.text.trim().isEmpty ? null : _postalCodeController.text.trim(),
      photoPath: _selectedPhoto?.path,
    );

    setState(() {
      _isSaving = false;
    });

    if (mounted) {
      if (success) {
        setState(() {
          _isEditing = false;
          _selectedPhoto = null;
        });
        SweetAlert.showSuccess(
          context: context,
          title: 'Success',
          message: 'Profile updated successfully!',
        );
        // Reload profile to get updated data
        provider.loadProfile(userId: user.id);
      } else {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: provider.error ?? 'Failed to update profile',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'My Profile',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          Consumer<StudentPortalProvider>(
            builder: (context, provider, child) {
              final profile = provider.profile;
              if (profile == null) return const SizedBox.shrink();
              
              return IconButton(
                icon: Icon(_isEditing ? Icons.close : Icons.edit),
                onPressed: () {
                  if (_isEditing) {
                    setState(() {
                      _isEditing = false;
                      _selectedPhoto = null;
                      _loadProfileData(profile);
                    });
                  } else {
                    setState(() {
                      _isEditing = true;
                      _loadProfileData(profile);
                    });
                  }
                },
                tooltip: _isEditing ? 'Cancel' : 'Edit',
              );
            },
          ),
        ],
      ),
      drawer: const RoleBasedDrawer(),
      body: Consumer<StudentPortalProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.profile == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final profile = provider.profile;
          final initial = profile?.firstName.isNotEmpty == true
              ? profile!.firstName[0].toUpperCase()
              : (user?.fullName.isNotEmpty == true
                  ? user!.fullName[0].toUpperCase()
                  : 'S');

          return Form(
            key: _formKey,
            child: SingleChildScrollView(
              child: Column(
                children: [
                  // Profile Header
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          AppConstants.primaryColor,
                          AppConstants.primaryColor.withOpacity(0.8),
                        ],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                    ),
                    child: Column(
                      children: [
                        Stack(
                          children: [
                            CircleAvatar(
                              radius: 50,
                              backgroundColor: Colors.white,
                              backgroundImage: _selectedPhoto != null
                                  ? FileImage(_selectedPhoto!)
                                  : (profile?.photo != null && 
                                      profile!.photo!.isNotEmpty && 
                                      profile.photo != '0' &&
                                      profile.photo!.trim().isNotEmpty
                                      ? NetworkImage(
                                          '${AppConstants.baseUrl.replaceAll('/api', '')}/${profile.photo}')
                                      : null),
                              child: _selectedPhoto == null &&
                                  (profile?.photo == null || 
                                   profile!.photo!.isEmpty ||
                                   profile.photo == '0')
                                  ? Text(
                                      initial,
                                      style: GoogleFonts.montserrat(
                                        fontSize: 40,
                                        fontWeight: FontWeight.bold,
                                        color: AppConstants.primaryColor,
                                      ),
                                    )
                                  : null,
                            ),
                            if (_isEditing)
                              Positioned(
                                bottom: 0,
                                right: 0,
                                child: Container(
                                  decoration: BoxDecoration(
                                    color: AppConstants.primaryColor,
                                    shape: BoxShape.circle,
                                    border: Border.all(color: Colors.white, width: 2),
                                  ),
                                  child: IconButton(
                                    icon: const Icon(Icons.camera_alt, color: Colors.white, size: 20),
                                    onPressed: _showImagePicker,
                                  ),
                                ),
                              ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          profile?.fullName ?? user?.fullName ?? 'Student',
                          style: GoogleFonts.montserrat(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 8),
                        if (profile?.className != null)
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 16, vertical: 8),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              profile!.className!,
                              style: GoogleFonts.montserrat(
                                fontSize: 14,
                                color: Colors.white,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                  // Profile Information
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildSectionTitle('Personal Information'),
                        const SizedBox(height: 12),
                        if (profile != null) ...[
                          _buildReadOnlyCard('Student ID', profile.studentId, Icons.badge_rounded),
                          _buildReadOnlyCard('Admission No', profile.admissionNo, Icons.numbers_rounded),
                          if (_isEditing) ...[
                            _buildEditableField(
                              'Email',
                              _emailController,
                              Icons.email_rounded,
                              TextInputType.emailAddress,
                              validator: (value) {
                                if (value != null && value.isNotEmpty) {
                                  if (!value.contains('@')) {
                                    return 'Please enter a valid email';
                                  }
                                }
                                return null;
                              },
                            ),
                            _buildEditableField(
                              'Phone',
                              _phoneController,
                              Icons.phone_rounded,
                              TextInputType.phone,
                            ),
                          ] else ...[
                            _buildReadOnlyCard('Email', profile.email ?? 'N/A', Icons.email_rounded),
                            _buildReadOnlyCard('Phone', profile.phone ?? 'N/A', Icons.phone_rounded),
                          ],
                          _buildReadOnlyCard(
                            'Date of Birth',
                            DateFormat('MMM d, yyyy').format(profile.dateOfBirth),
                            Icons.cake_rounded,
                          ),
                          _buildReadOnlyCard('Gender', profile.gender, Icons.person_rounded),
                          _buildReadOnlyCard('Status', profile.status, Icons.info_rounded),
                        ] else ...[
                          _buildReadOnlyCard('Email', user?.email ?? 'N/A', Icons.email_rounded),
                          _buildReadOnlyCard('Username', user?.username ?? 'N/A', Icons.person_rounded),
                        ],
                        const SizedBox(height: 24),
                        if (profile != null) ...[
                          _buildSectionTitle('Address Information'),
                          const SizedBox(height: 12),
                          if (_isEditing) ...[
                            _buildEditableField(
                              'Address',
                              _addressController,
                              Icons.home_rounded,
                              TextInputType.streetAddress,
                              maxLines: 3,
                            ),
                            _buildEditableField(
                              'City',
                              _cityController,
                              Icons.location_city_rounded,
                              TextInputType.text,
                            ),
                            _buildEditableField(
                              'State',
                              _stateController,
                              Icons.map_rounded,
                              TextInputType.text,
                            ),
                            _buildEditableField(
                              'Postal Code',
                              _postalCodeController,
                              Icons.markunread_mailbox_rounded,
                              TextInputType.number,
                            ),
                          ] else ...[
                            _buildReadOnlyCard('Address', profile.address ?? 'N/A', Icons.home_rounded),
                            _buildReadOnlyCard('City', profile.city ?? 'N/A', Icons.location_city_rounded),
                            _buildReadOnlyCard('State', profile.state ?? 'N/A', Icons.map_rounded),
                            _buildReadOnlyCard('Postal Code', profile.postalCode ?? 'N/A', Icons.markunread_mailbox_rounded),
                          ],
                          const SizedBox(height: 24),
                          _buildSectionTitle('Academic Information'),
                          const SizedBox(height: 12),
                          _buildReadOnlyCard('Class', profile.className ?? 'N/A', Icons.class_rounded),
                          _buildReadOnlyCard('Section', profile.sectionName ?? 'N/A', Icons.group_rounded),
                          _buildReadOnlyCard(
                            'Admission Date',
                            DateFormat('MMM d, yyyy').format(profile.admissionDate),
                            Icons.calendar_today_rounded,
                          ),
                          if (profile.branchName != null)
                            _buildReadOnlyCard('Branch', profile.branchName!, Icons.business_rounded),
                        ],
                        if (_isEditing) ...[
                          const SizedBox(height: 24),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton(
                              onPressed: _isSaving ? null : _saveProfile,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppConstants.primaryColor,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 16),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                elevation: 2,
                              ),
                              child: _isSaving
                                  ? const SizedBox(
                                      height: 20,
                                      width: 20,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                      ),
                                    )
                                  : Text(
                                      'Save Changes',
                                      style: GoogleFonts.montserrat(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: GoogleFonts.montserrat(
        fontSize: 18,
        fontWeight: FontWeight.bold,
        color: Colors.grey[900],
      ),
    );
  }

  Widget _buildReadOnlyCard(String label, String value, IconData icon) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: AppConstants.primaryColor.withOpacity(0.1),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: AppConstants.primaryColor, size: 20),
        ),
        title: Text(
          label,
          style: GoogleFonts.montserrat(
            fontSize: 12,
            color: Colors.grey[600],
            fontWeight: FontWeight.w500,
          ),
        ),
        subtitle: Text(
          value,
          style: GoogleFonts.montserrat(
            fontSize: 15,
            fontWeight: FontWeight.w600,
            color: Colors.grey[900],
          ),
        ),
      ),
    );
  }

  Widget _buildEditableField(
    String label,
    TextEditingController controller,
    IconData icon,
    TextInputType keyboardType, {
    String? Function(String?)? validator,
    int maxLines = 1,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppConstants.primaryColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(icon, color: AppConstants.primaryColor, size: 18),
                ),
                const SizedBox(width: 12),
                Text(
                  label,
                  style: GoogleFonts.montserrat(
                    fontSize: 12,
                    color: Colors.grey[600],
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            TextFormField(
              controller: controller,
              keyboardType: keyboardType,
              maxLines: maxLines,
              validator: validator,
              style: GoogleFonts.montserrat(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                color: Colors.grey[900],
              ),
              decoration: InputDecoration(
                hintText: 'Enter $label',
                hintStyle: GoogleFonts.montserrat(color: Colors.grey[400]),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: BorderSide(color: Colors.grey[300]!),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: BorderSide(color: Colors.grey[300]!),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: BorderSide(color: AppConstants.primaryColor, width: 2),
                ),
                errorBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: const BorderSide(color: Colors.red, width: 2),
                ),
                focusedErrorBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: const BorderSide(color: Colors.red, width: 2),
                ),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
