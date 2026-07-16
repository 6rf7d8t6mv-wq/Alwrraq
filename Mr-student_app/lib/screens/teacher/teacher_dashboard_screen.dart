import 'package:flutter/material.dart';

class TeacherDashboardScreen extends StatelessWidget {
  const TeacherDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(centerTitle: true, title: const Text('لوحة المعلم')),
      body: Center(
        child: Text(
          'لوحة المعلم',
          style: Theme.of(context).textTheme.headlineMedium,
        ),
      ),
    );
  }
}
