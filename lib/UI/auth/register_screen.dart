import 'package:flutter/material.dart';

import '../../services/auth_service.dart';
import 'auth_card.dart' hide kTeal;

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _ageController = TextEditingController();
  final TextEditingController _cityController = TextEditingController();
  final TextEditingController _nationalityController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  
  final AuthService _authService = AuthService();
  String? selectedSpecialization;
  String? selectedEducationLevel;
  bool isDragging = false;
  bool _isLoading = false;
  String _errorMessage = '';

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

  Future<void> _register() async {
    // Validate required fields
    if (_usernameController.text.isEmpty ||
        _emailController.text.isEmpty ||
        _phoneController.text.isEmpty ||
        _passwordController.text.isEmpty) {
      setState(() {
        _errorMessage = 'Please fill in all required fields';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      final userData = {
        'username': _usernameController.text.trim(),
        'email': _emailController.text.trim(),
        'phone_number': _phoneController.text.trim(),
        'age': _ageController.text.isNotEmpty ? int.tryParse(_ageController.text) : null,
        'city': _cityController.text.trim(),
        'nationality': _nationalityController.text.trim(),
        'specialization': selectedSpecialization,
        'education_level': selectedEducationLevel,
        'password': _passwordController.text,
      };

      final response = await _authService.register(userData);

      if (response.isSuccess) {
        if (mounted) {
          Navigator.pushReplacementNamed(context, '/chat');
        }
      } else {
        setState(() {
          _errorMessage = response.message;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Registration failed. Please try again.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Widget _buildDropdownField({
    required String label,
    required String hint,
    required IconData icon,
    required List<String> items,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(color: Colors.white, fontSize: 14)),
        const SizedBox(height: 4),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(8),
          ),
          child: DropdownButtonFormField<String>(
            value: label == 'Specialization'
                ? selectedSpecialization
                : selectedEducationLevel,
            decoration: InputDecoration(
              hintText: hint,
              filled: true,
              fillColor: Colors.white,
              suffixIcon: Icon(icon, color: kTeal),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 20,
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: BorderSide.none,
              ),
            ),
            items: items.map((String item) {
              return DropdownMenuItem<String>(value: item, child: Text(item));
            }).toList(),
            onChanged: (String? newValue) {
              setState(() {
                if (label == 'Specialization') {
                  selectedSpecialization = newValue;
                } else {
                  selectedEducationLevel = newValue;
                }
              });
            },
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Stack(
        children: [
          SafeArea(
            child: Column(
              children: [
                // const SizedBox(height: 24),
                // const SizedBox(height: 24),
                if (!isDragging)
                  Image.asset(
                    'assets/images/splash/medico_logo.jpg',
                    height: 150,
                    width: 150,
                  ),

                // const Spacer(),
                Expanded(
                  child: NotificationListener<ScrollNotification>(
                    onNotification: (ScrollNotification notification) {
                      if (notification is ScrollStartNotification) {
                        setState(() {
                          isDragging = true;
                        });
                      } else if (notification is ScrollEndNotification) {
                        setState(() {
                          isDragging = true;
                        });
                      }
                      return true;
                    },
                    child: DraggableScrollableSheet(
                      initialChildSize: 1,
                      minChildSize: 0.5,
                      maxChildSize: 1.0,
                      builder: (context, scrollController) {
                        return Container(
                          width: double.infinity,
                          decoration: BoxDecoration(
                            color: kTeal,
                            borderRadius: const BorderRadius.only(
                              topLeft: Radius.circular(50),
                              topRight: Radius.circular(50),
                            ),
                          ),
                          child: Column(
                            children: [
                              // Drag handle
                              Container(
                                margin: const EdgeInsets.only(top: 12),
                                width: 40,
                                height: 4,
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.6),
                                  borderRadius: BorderRadius.circular(2),
                                ),
                              ),
                              Expanded(
                                child: SingleChildScrollView(
                                  controller: scrollController,
                                  padding: const EdgeInsets.fromLTRB(
                                    24,
                                    32,
                                    24,
                                    40,
                                  ),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Center(
                                        child: Column(
                                          children: [
                                            const Text(
                                              'Register to Continue',
                                              style: TextStyle(
                                                fontSize: 22,
                                                fontWeight: FontWeight.w700,
                                                color: Colors.white,
                                              ),
                                            ),
                                            const SizedBox(height: 6),
                                            Container(
                                              height: 4,
                                              width: 60,
                                              decoration: BoxDecoration(
                                                color: Colors.white,
                                                borderRadius:
                                                    BorderRadius.circular(2),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      const SizedBox(height: 32),

                                      LabeledField(
                                        label: 'Username',
                                        hint: 'your username...',
                                        icon: Icons.person_outlined,
                                        controller: _usernameController,
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'Email',
                                        hint: 'your email id...',
                                        icon: Icons.email_outlined,
                                        controller: _emailController,
                                        keyboardType: TextInputType.emailAddress,
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'Phone Number',
                                        hint: 'your phone number...',
                                        icon: Icons.phone_outlined,
                                        controller: _phoneController,
                                        keyboardType: TextInputType.phone,
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'Age',
                                        hint: 'your age...',
                                        icon: Icons.calendar_today_outlined,
                                        controller: _ageController,
                                        keyboardType: TextInputType.number,
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'City',
                                        hint: 'your city...',
                                        icon: Icons.location_city_outlined,
                                        controller: _cityController,
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'Nationality',
                                        hint: 'your nationality...',
                                        icon: Icons.flag_outlined,
                                        controller: _nationalityController,
                                      ),
                                      const SizedBox(height: 16),
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
                                      ),
                                      const SizedBox(height: 16),
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
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'Password',
                                        hint: 'password',
                                        icon: Icons.lock_outline,
                                        obscure: true,
                                        controller: _passwordController,
                                      ),

                                      if (_errorMessage.isNotEmpty) ...[
                                        const SizedBox(height: 16),
                                        Container(
                                          width: double.infinity,
                                          padding: const EdgeInsets.all(12),
                                          decoration: BoxDecoration(
                                            color: Colors.red.withOpacity(0.1),
                                            borderRadius: BorderRadius.circular(8),
                                            border: Border.all(color: Colors.red.withOpacity(0.3)),
                                          ),
                                          child: Text(
                                            _errorMessage,
                                            style: const TextStyle(
                                              color: Colors.red,
                                              fontSize: 14,
                                            ),
                                            textAlign: TextAlign.center,
                                          ),
                                        ),
                                      ],

                                      const SizedBox(height: 32),
                                      PrimaryButton(
                                        text: _isLoading ? 'REGISTERING...' : 'REGISTER',
                                        onTap: _isLoading ? () {} : _register,
                                      ),
                                      const SizedBox(height: 24),
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.center,
                                        children: [
                                          const Text(
                                            'Already have an account? ',
                                            style: TextStyle(
                                              color: Colors.white70,
                                            ),
                                          ),
                                          GestureDetector(
                                            onTap: () =>
                                                Navigator.of(context).pop(),
                                            child: const Text(
                                              'Login',
                                              style: TextStyle(
                                                color: Colors.white,
                                                decoration:
                                                    TextDecoration.underline,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 40),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
