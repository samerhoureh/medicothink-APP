import 'package:flutter/material.dart';
import '../auth/auth_card.dart';

class ProfileSettingsScreen extends StatefulWidget {
  const ProfileSettingsScreen({super.key});

  @override
  State<ProfileSettingsScreen> createState() => _ProfileSettingsScreenState();
}

class _ProfileSettingsScreenState extends State<ProfileSettingsScreen> {
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _ageController = TextEditingController();
  final TextEditingController _cityController = TextEditingController();
  final TextEditingController _nationalityController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  
  String? _selectedSpecialization;
  String? _selectedEducationLevel;
  
  bool _isLoading = false;
  bool _isEditing = false;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  void _loadUserData() {
    // Static user data - in real app, this would come from user service
    _usernameController.text = 'Dr. Sarah Johnson';
    _emailController.text = 'sarah.johnson@medico.com';
    _phoneController.text = '+1 (555) 123-4567';
    _ageController.text = '32';
    _cityController.text = 'New York';
    _nationalityController.text = 'American';
    _passwordController.text = '••••••••';
    _selectedSpecialization = 'Medical';
    _selectedEducationLevel = 'Master';
  }

  @override
  void dispose() {
    _usernameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _ageController.dispose();
    _cityController.dispose();
    _nationalityController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _updateProfile() async {
    if (!_isEditing) {
      setState(() {
        _isEditing = true;
      });
      return;
    }

    // Validate required fields
    if (_usernameController.text.trim().isEmpty ||
        _emailController.text.trim().isEmpty ||
        _phoneController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please fill in all required fields'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    // Simulate API call
    await Future.delayed(const Duration(seconds: 2));

    setState(() {
      _isLoading = false;
      _isEditing = false;
    });

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Profile updated successfully!'),
        backgroundColor: kTeal,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: kNavy),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text(
          'Profile Settings',
          style: TextStyle(
            color: kNavy,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              // Profile Image Section
              Center(
                child: Column(
                  children: [
                    Stack(
                      children: [
                        Container(
                          width: 120,
                          height: 120,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(60),
                            image: const DecorationImage(
                              image: AssetImage('assets/images/splash/medico_logo.jpg'),
                              fit: BoxFit.cover,
                            ),
                          ),
                        ),
                        if (_isEditing)
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              width: 36,
                              height: 36,
                              decoration: BoxDecoration(
                                color: kTeal,
                                borderRadius: BorderRadius.circular(18),
                                border: Border.all(color: Colors.white, width: 3),
                              ),
                              child: const Icon(
                                Icons.camera_alt,
                                color: Colors.white,
                                size: 18,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Text(
                      _isEditing ? 'Tap to change photo' : 'Profile Photo',
                      style: TextStyle(
                        color: _isEditing ? kTeal : Colors.grey,
                        fontSize: 14,
                        fontWeight: _isEditing ? FontWeight.w600 : FontWeight.normal,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 32),

              // Profile Information Form
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.grey.withOpacity(0.1),
                      spreadRadius: 1,
                      blurRadius: 10,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                padding: const EdgeInsets.all(24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Personal Information',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w600,
                        color: kNavy,
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Username
                    _buildInfoField(
                      label: 'Username',
                      controller: _usernameController,
                      icon: Icons.person_outline,
                      isRequired: true,
                    ),
                    const SizedBox(height: 20),

                    // Email
                    _buildInfoField(
                      label: 'Email',
                      controller: _emailController,
                      icon: Icons.email_outlined,
                      keyboardType: TextInputType.emailAddress,
                      isRequired: true,
                    ),
                    const SizedBox(height: 20),

                    // Phone
                    _buildInfoField(
                      label: 'Phone Number',
                      controller: _phoneController,
                      icon: Icons.phone_outlined,
                      keyboardType: TextInputType.phone,
                      isRequired: true,
                    ),
                    const SizedBox(height: 20),

                    // Age
                    _buildInfoField(
                      label: 'Age',
                      controller: _ageController,
                      icon: Icons.calendar_today_outlined,
                      keyboardType: TextInputType.number,
                    ),
                    const SizedBox(height: 20),

                    // City
                    _buildInfoField(
                      label: 'City',
                      controller: _cityController,
                      icon: Icons.location_city_outlined,
                    ),
                    const SizedBox(height: 20),

                    // Nationality
                    _buildInfoField(
                      label: 'Nationality',
                      controller: _nationalityController,
                      icon: Icons.flag_outlined,
                    ),
                    const SizedBox(height: 20),

                    // Specialization Dropdown
                    _buildDropdownField(
                      label: 'Specialization',
                      hint: 'Select specialization...',
                      icon: Icons.medical_services_outlined,
                      items: [
                        'Medical',
                        'Dentist',
                        'Pharmacy',
                        'X-ray',
                      ],
                      value: _selectedSpecialization,
                      onChanged: (value) {
                        setState(() {
                          _selectedSpecialization = value;
                        });
                      },
                    ),
                    const SizedBox(height: 20),

                    // Education Level Dropdown
                    _buildDropdownField(
                      label: 'Education Level',
                      hint: 'Select education level...',
                      icon: Icons.school_outlined,
                      items: [
                        'High School',
                        'Bachelor',
                        'Master',
                        'PhD',
                        'Other',
                      ],
                      value: _selectedEducationLevel,
                      onChanged: (value) {
                        setState(() {
                          _selectedEducationLevel = value;
                        });
                      },
                    ),
                    const SizedBox(height: 20),

                    // Password
                    _buildInfoField(
                      label: 'Password',
                      controller: _passwordController,
                      icon: Icons.lock_outline,
                      obscure: true,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 32),

              // Update Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kTeal,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 0,
                  ),
                  onPressed: _isLoading ? null : _updateProfile,
                  child: _isLoading
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : Text(
                          _isEditing ? 'Save Changes' : 'Edit Profile',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                ),
              ),

              if (_isEditing) ...[
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red,
                      side: const BorderSide(color: Colors.red),
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    onPressed: () {
                      setState(() {
                        _isEditing = false;
                        _loadUserData(); // Reset to original data
                      });
                    },
                    child: const Text(
                      'Cancel',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoField({
    required String label,
    required TextEditingController controller,
    required IconData icon,
    TextInputType? keyboardType,
    bool isRequired = false,
    bool obscure = false,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text(
              label,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: Colors.black87,
              ),
            ),
            if (isRequired)
              const Text(
                ' *',
                style: TextStyle(
                  color: Colors.red,
                  fontSize: 14,
                ),
              ),
          ],
        ),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          enabled: _isEditing,
          keyboardType: keyboardType,
          obscureText: obscure,
          decoration: InputDecoration(
            prefixIcon: Icon(icon, color: kTeal),
            filled: true,
            fillColor: _isEditing ? Colors.white : Colors.grey.withOpacity(0.1),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(
                color: _isEditing ? Colors.grey.withOpacity(0.3) : Colors.transparent,
              ),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(
                color: _isEditing ? Colors.grey.withOpacity(0.3) : Colors.transparent,
              ),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: kTeal, width: 2),
            ),
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 16,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildDropdownField({
    required String label,
    required String hint,
    required IconData icon,
    required List<String> items,
    String? value,
    required Function(String?) onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w500,
            color: Colors.black87,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          decoration: BoxDecoration(
            color: _isEditing ? Colors.white : Colors.grey.withOpacity(0.1),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: _isEditing ? Colors.grey.withOpacity(0.3) : Colors.transparent,
            ),
          ),
          child: DropdownButtonFormField<String>(
            value: value,
            decoration: InputDecoration(
              hintText: hint,
              filled: true,
              fillColor: Colors.transparent,
              prefixIcon: Icon(icon, color: kTeal),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 16,
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide.none,
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide.none,
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: kTeal, width: 2),
              ),
            ),
            items: items.map((String item) {
              return DropdownMenuItem<String>(value: item, child: Text(item));
            }).toList(),
            onChanged: onChanged,
          ),
        ),
      ],
    );
  }
} 