import 'package:flutter/material.dart';
import 'package:medicothink/UI/splash/splash_screen.dart';

import 'UI/auth/login_screen.dart';
import 'UI/auth/register_screen.dart';
import 'UI/auth/otp_login_screen.dart';
import 'UI/home/chat_screen.dart';
import 'UI/home/profile_settings_screen.dart';
import 'UI/splash/onpording2.dart';
import 'UI/home/archived_conversations_screen.dart';
import 'UI/home/conversation_summary_screen.dart';
class AppRouter {
  static Route<dynamic> generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case '/':
        return MaterialPageRoute(builder: (_) => const SplashScreen());
        case '/login':
        return MaterialPageRoute(builder: (_) => const LoginScreen());
      case '/otp-login':
        return MaterialPageRoute(builder: (_) => const OtpLoginScreen());
      case '/profile-settings':
        return MaterialPageRoute(builder: (_) => const ProfileSettingsScreen());
      case '/register':
        return MaterialPageRoute(builder: (_) => const RegisterScreen());
      case '/onboarding2':
        return MaterialPageRoute(builder: (_) => const Onboarding2Screen());
      case '/chat':
        return MaterialPageRoute(builder: (_) => const ChatScreen());
      case '/archived':
        return MaterialPageRoute(builder: (_) => const ArchivedConversationsScreen());

      case '/conversation-summary':
        final args = settings.arguments as Map<String, String>?;
        if (args != null) {
          return MaterialPageRoute(
            builder: (_) => ConversationSummaryScreen(
              conversationId: args['conversationId']!,
              conversationTitle: args['conversationTitle']!,
            ),
          );
        }
        return MaterialPageRoute(
          builder: (_) => Scaffold(
            body: Center(
              child: Text('Invalid conversation summary arguments'),
            ),
          ),
        );

      default:
        return MaterialPageRoute(
          builder: (_) => Scaffold(
            body: Center(
              child: Text('No route defined for ${settings.name}'),
            ),
          ),
        );
    }
  }
}
