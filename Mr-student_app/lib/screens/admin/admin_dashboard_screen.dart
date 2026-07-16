import 'package:flutter/material.dart';

class AdminDashboardScreen extends StatelessWidget {
  const AdminDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(centerTitle: true, title: const Text('لوحة الإدارة')),
      body: Center(
        child: Text(
          'لوحة الإدارة',
          style: Theme.of(context).textTheme.headlineMedium,
        ),
      ),
    );
  }
}
