import 'package:flutter/material.dart';
import '../../services/subscription_service.dart';

class SubscriptionAlertWidget extends StatelessWidget {
  final SubscriptionAlert alert;
  final VoidCallback? onRenewPressed;
  final VoidCallback? onDismissPressed;

  const SubscriptionAlertWidget({
    super.key,
    required this.alert,
    this.onRenewPressed,
    this.onDismissPressed,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: alert.type == SubscriptionAlertType.expired 
            ? Colors.red.withOpacity(0.1)
            : Colors.orange.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: alert.type == SubscriptionAlertType.expired 
              ? Colors.red.withOpacity(0.3)
              : Colors.orange.withOpacity(0.3),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                alert.type == SubscriptionAlertType.expired 
                    ? Icons.error_outline
                    : Icons.warning_outlined,
                color: alert.type == SubscriptionAlertType.expired 
                    ? Colors.red
                    : Colors.orange,
                size: 24,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  alert.title,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: alert.type == SubscriptionAlertType.expired 
                        ? Colors.red[700]
                        : Colors.orange[700],
                  ),
                ),
              ),
              if (onDismissPressed != null)
                IconButton(
                  icon: const Icon(Icons.close, size: 20),
                  onPressed: onDismissPressed,
                  color: Colors.grey[600],
                ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            alert.message,
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[700],
              height: 1.4,
            ),
          ),
          if (onRenewPressed != null) ...[
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: alert.type == SubscriptionAlertType.expired 
                      ? Colors.red
                      : Colors.orange,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
                onPressed: onRenewPressed,
                child: const Text(
                  'Renew Subscription',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}