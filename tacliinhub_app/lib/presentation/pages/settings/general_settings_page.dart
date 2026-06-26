import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/settings_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';
import '../../../data/models/settings_models.dart';

class GeneralSettingsPage extends StatefulWidget {
  const GeneralSettingsPage({super.key});

  @override
  State<GeneralSettingsPage> createState() => _GeneralSettingsPageState();
}

class _GeneralSettingsPageState extends State<GeneralSettingsPage> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _schoolNameController;
  late TextEditingController _schoolNameSomaliController;
  late TextEditingController _schoolEmailController;
  late TextEditingController _schoolPhoneController;
  late TextEditingController _schoolAddressController;
  late TextEditingController _currencyController;
  late TextEditingController _currencySymbolController;
  late TextEditingController _timezoneController;
  String _language = 'en';
  String _dateFormat = 'd-m-Y';

  @override
  void initState() {
    super.initState();
    _schoolNameController = TextEditingController();
    _schoolNameSomaliController = TextEditingController();
    _schoolEmailController = TextEditingController();
    _schoolPhoneController = TextEditingController();
    _schoolAddressController = TextEditingController();
    _currencyController = TextEditingController(text: 'USD');
    _currencySymbolController = TextEditingController(text: '\$');
    _timezoneController = TextEditingController(text: 'Africa/Mogadishu');

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadSettings();
    });
  }

  void _loadSettings() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<SettingsProvider>();
    provider.loadGeneralSettings(userId: user?.id);
  }

  @override
  void dispose() {
    _schoolNameController.dispose();
    _schoolNameSomaliController.dispose();
    _schoolEmailController.dispose();
    _schoolPhoneController.dispose();
    _schoolAddressController.dispose();
    _currencyController.dispose();
    _currencySymbolController.dispose();
    _timezoneController.dispose();
    super.dispose();
  }

  Future<void> _saveSettings() async {
    if (!_formKey.currentState!.validate()) return;

    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user == null) {
      SweetAlert.showError(
        context: context,
        title: 'Error',
        message: 'User not logged in',
      );
      return;
    }

    final provider = context.read<SettingsProvider>();
    final settings = GeneralSettings(
      id: provider.generalSettings?.id ?? 0,
      schoolName: _schoolNameController.text.trim(),
      schoolNameSomali: _schoolNameSomaliController.text.trim().isEmpty
          ? null
          : _schoolNameSomaliController.text.trim(),
      schoolEmail: _schoolEmailController.text.trim().isEmpty
          ? null
          : _schoolEmailController.text.trim(),
      schoolPhone: _schoolPhoneController.text.trim().isEmpty
          ? null
          : _schoolPhoneController.text.trim(),
      schoolAddress: _schoolAddressController.text.trim().isEmpty
          ? null
          : _schoolAddressController.text.trim(),
      currency: _currencyController.text.trim(),
      currencySymbol: _currencySymbolController.text.trim(),
      timezone: _timezoneController.text.trim(),
      language: _language,
      dateFormat: _dateFormat,
    );

    final success = await provider.saveGeneralSettings(
      settings,
      userId: user.id,
    );

    if (mounted) {
      if (success) {
        SweetAlert.showSuccess(
          context: context,
          title: 'Success',
          message: 'General settings saved successfully',
        );
      } else {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: provider.error ?? 'Failed to save settings',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'General Settings',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.blue,
        elevation: 0,
      ),
      body: Consumer<SettingsProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.generalSettings == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.generalSettings == null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading settings'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _loadSettings,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          // Load data into controllers if settings exist
          if (provider.generalSettings != null) {
            final settings = provider.generalSettings!;
            _schoolNameController.text = settings.schoolName;
            _schoolNameSomaliController.text = settings.schoolNameSomali ?? '';
            _schoolEmailController.text = settings.schoolEmail ?? '';
            _schoolPhoneController.text = settings.schoolPhone ?? '';
            _schoolAddressController.text = settings.schoolAddress ?? '';
            _currencyController.text = settings.currency;
            _currencySymbolController.text = settings.currencySymbol;
            _timezoneController.text = settings.timezone;
            _language = settings.language;
            _dateFormat = settings.dateFormat;
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  TextFormField(
                    controller: _schoolNameController,
                    decoration: InputDecoration(
                      labelText: 'School Name *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Please enter school name';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _schoolNameSomaliController,
                    decoration: InputDecoration(
                      labelText: 'School Name (Somali)',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _schoolEmailController,
                    decoration: InputDecoration(
                      labelText: 'School Email',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    keyboardType: TextInputType.emailAddress,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _schoolPhoneController,
                    decoration: InputDecoration(
                      labelText: 'School Phone',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    keyboardType: TextInputType.phone,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _schoolAddressController,
                    decoration: InputDecoration(
                      labelText: 'School Address',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: TextFormField(
                          controller: _currencyController,
                          decoration: InputDecoration(
                            labelText: 'Currency *',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Required';
                            }
                            return null;
                          },
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: TextFormField(
                          controller: _currencySymbolController,
                          decoration: InputDecoration(
                            labelText: 'Currency Symbol *',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Required';
                            }
                            return null;
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _timezoneController,
                    decoration: InputDecoration(
                      labelText: 'Timezone *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Required';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    decoration: InputDecoration(
                      labelText: 'Language',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    initialValue: _language,
                    items: const [
                      DropdownMenuItem(value: 'en', child: Text('English')),
                      DropdownMenuItem(value: 'so', child: Text('Somali')),
                      DropdownMenuItem(value: 'ar', child: Text('Arabic')),
                    ],
                    onChanged: (value) {
                      if (value != null) {
                        setState(() {
                          _language = value;
                        });
                      }
                    },
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    decoration: InputDecoration(
                      labelText: 'Date Format',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    initialValue: _dateFormat,
                    items: const [
                      DropdownMenuItem(
                        value: 'd-m-Y',
                        child: Text('DD-MM-YYYY'),
                      ),
                      DropdownMenuItem(
                        value: 'Y-m-d',
                        child: Text('YYYY-MM-DD'),
                      ),
                      DropdownMenuItem(
                        value: 'm/d/Y',
                        child: Text('MM/DD/YYYY'),
                      ),
                    ],
                    onChanged: (value) {
                      if (value != null) {
                        setState(() {
                          _dateFormat = value;
                        });
                      }
                    },
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: provider.isLoading ? null : _saveSettings,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: provider.isLoading
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(
                                Colors.white,
                              ),
                            ),
                          )
                        : Text(
                            'Save Settings',
                            style: GoogleFonts.montserrat(
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                            ),
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
}
