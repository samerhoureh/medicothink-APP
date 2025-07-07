import 'package:flutter/material.dart';

import '../home/chat_drawer.dart';
import 'auth_card.dart' hide kTeal;

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  String? selectedSpecialization;
  String? selectedEducationLevel;
  bool isDragging = false;

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
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'Email',
                                        hint: 'your email id...',
                                        icon: Icons.email_outlined,
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'Phone Number',
                                        hint: 'your phone number...',
                                        icon: Icons.phone_outlined,
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'Age',
                                        hint: 'your age...',
                                        icon: Icons.calendar_today_outlined,
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'City',
                                        hint: 'your city...',
                                        icon: Icons.location_city_outlined,
                                      ),
                                      const SizedBox(height: 16),
                                      LabeledField(
                                        label: 'Nationality',
                                        hint: 'your nationality...',
                                        icon: Icons.flag_outlined,
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
                                      ),
                                      const SizedBox(height: 32),
                                      PrimaryButton(
                                        text: 'REGISTER',
                                        onTap: () {
                                          Navigator.pushReplacementNamed(
                                            context,
                                            '/chat',
                                          );
                                        },
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
