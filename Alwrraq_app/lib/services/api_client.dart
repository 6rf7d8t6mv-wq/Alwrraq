import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiClient {
  ApiClient._();

  static const String baseUrl = 'http://127.0.0.1:8000/api';

  static String? token;
  static Map<String, dynamic>? user;

  static Map<String, String> get _jsonHeaders => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  static Map<String, String> get authHeaders => {
    'Accept': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  };

  static void logout() {
    token = null;
    user = null;
  }

  static Future<String?> login({
    required String phone,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: _jsonHeaders,
        body: jsonEncode({'phone': phone, 'password': password}),
      );

      return _handleAuthResponse(response);
    } catch (_) {
      return 'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000';
    }
  }

  static Future<String?> register({
    required String name,
    required String phone,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/register'),
        headers: _jsonHeaders,
        body: jsonEncode({
          'name': name,
          'phone': phone,
          'password': password,
          'password_confirmation': passwordConfirmation,
        }),
      );

      return _handleAuthResponse(response);
    } catch (_) {
      return 'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000';
    }
  }

  static Future<Map<String, dynamic>> uploadFile({
    required List<int> bytes,
    required String filename,
    required String type,
    required String service,
  }) async {
    if (token == null) {
      return {'success': false, 'message': 'سجل الدخول أولًا قبل رفع الملفات.'};
    }

    try {
      final request =
          http.MultipartRequest('POST', Uri.parse('$baseUrl/upload-file'))
            ..headers.addAll(authHeaders)
            ..fields['type'] = type
            ..fields['service'] = service
            ..files.add(
              http.MultipartFile.fromBytes('file', bytes, filename: filename),
            );

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      final data = _decode(response.body);

      if (response.statusCode >= 200 &&
          response.statusCode < 300 &&
          data['success'] == true) {
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static Future<Map<String, dynamic>> updateFile({
    required int fileId,
    String? bindingType,
    int? copies,
  }) async {
    try {
      final payload = <String, dynamic>{};
      if (bindingType != null) {
        payload['binding_type'] = bindingType;
      }
      if (copies != null) {
        payload['copies'] = copies;
      }

      final response = await http.patch(
        Uri.parse('$baseUrl/order-files/$fileId'),
        headers: {...authHeaders, 'Content-Type': 'application/json'},
        body: jsonEncode(payload),
      );
      final data = _decode(response.body);

      if (response.statusCode >= 200 &&
          response.statusCode < 300 &&
          data['success'] == true) {
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static Future<Map<String, dynamic>> deleteFile({required int fileId}) async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/order-files/$fileId'),
        headers: authHeaders,
      );
      final data = _decode(response.body);

      if (response.statusCode >= 200 &&
          response.statusCode < 300 &&
          data['success'] == true) {
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static Future<Map<String, dynamic>> cart({required int orderId}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/cart/$orderId'),
        headers: authHeaders,
      );
      final data = _decode(response.body);

      if (response.statusCode >= 200 &&
          response.statusCode < 300 &&
          data['success'] == true) {
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static Future<Map<String, dynamic>> me() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/me'),
        headers: authHeaders,
      );
      final data = _decode(response.body);

      if (response.statusCode >= 200 &&
          response.statusCode < 300 &&
          data['success'] == true) {
        user = data['user'] is Map<String, dynamic>
            ? data['user'] as Map<String, dynamic>
            : user;
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static Future<Map<String, dynamic>> orders() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/orders'),
        headers: authHeaders,
      );
      final data = _decode(response.body);

      if (response.statusCode >= 200 &&
          response.statusCode < 300 &&
          data['success'] == true) {
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static Future<Map<String, dynamic>> pay({
    required int orderId,
    required String paymentMethod,
    String? cardName,
    String? cardNumber,
    String? cardExpiry,
    String? cardCvc,
  }) async {
    try {
      final payload = <String, dynamic>{'payment_method': paymentMethod};
      if (cardName != null) payload['card_name'] = cardName;
      if (cardNumber != null) payload['card_number'] = cardNumber;
      if (cardExpiry != null) payload['card_expiry'] = cardExpiry;
      if (cardCvc != null) payload['card_cvc'] = cardCvc;

      final response = await http.post(
        Uri.parse('$baseUrl/cart/$orderId/pay'),
        headers: {...authHeaders, 'Content-Type': 'application/json'},
        body: jsonEncode(payload),
      );
      final data = _decode(response.body);

      if (response.statusCode >= 200 &&
          response.statusCode < 300 &&
          data['success'] == true) {
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static Future<Map<String, dynamic>> updateProfile({
    required String name,
    required String phone,
  }) async {
    return _patchAccount(
      path: 'profile',
      payload: {'name': name, 'phone': phone},
    );
  }

  static Future<Map<String, dynamic>> updateAddress({
    required String city,
    required String district,
    required String street,
    required String postalCode,
  }) async {
    return _patchAccount(
      path: 'address',
      payload: {
        'city': city,
        'district': district,
        'street': street,
        'postal_code': postalCode,
      },
    );
  }

  static Future<Map<String, dynamic>> updatePassword({
    required String currentPassword,
    required String password,
    required String passwordConfirmation,
  }) async {
    return _patchAccount(
      path: 'password',
      payload: {
        'current_password': currentPassword,
        'password': password,
        'password_confirmation': passwordConfirmation,
      },
    );
  }

  static Future<Map<String, dynamic>> chatConversations() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/chat/conversations'),
        headers: authHeaders,
      );
      final data = _decode(response.body);

      if (response.statusCode >= 200 && response.statusCode < 300) {
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static Future<Map<String, dynamic>> chatMessages({
    required int conversationId,
  }) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/chat/conversations/$conversationId'),
        headers: authHeaders,
      );
      final data = _decode(response.body);

      if (response.statusCode >= 200 && response.statusCode < 300) {
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static Future<Map<String, dynamic>> sendChatMessage({
    required int conversationId,
    required String message,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/chat/conversations/$conversationId/messages'),
        headers: {...authHeaders, 'Content-Type': 'application/json'},
        body: jsonEncode({'message': message}),
      );
      final data = _decode(response.body);

      if (response.statusCode >= 200 && response.statusCode < 300) {
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static Future<Map<String, dynamic>> _patchAccount({
    required String path,
    required Map<String, dynamic> payload,
  }) async {
    try {
      final response = await http.patch(
        Uri.parse('$baseUrl/account/$path'),
        headers: {...authHeaders, 'Content-Type': 'application/json'},
        body: jsonEncode(payload),
      );
      final data = _decode(response.body);

      if (response.statusCode >= 200 &&
          response.statusCode < 300 &&
          data['success'] == true) {
        if (data['user'] is Map<String, dynamic>) {
          user = data['user'] as Map<String, dynamic>;
        }
        return data;
      }

      return {
        'success': false,
        'message': _extractMessage(data, response.statusCode),
      };
    } catch (_) {
      return {
        'success': false,
        'message':
            'تعذر الاتصال بـ Laravel. تأكد أن السيرفر يعمل على 127.0.0.1:8000',
      };
    }
  }

  static String? _handleAuthResponse(http.Response response) {
    final data = _decode(response.body);

    if (response.statusCode >= 200 &&
        response.statusCode < 300 &&
        data['success'] == true) {
      token = data['token']?.toString();
      user = data['user'] is Map<String, dynamic>
          ? data['user'] as Map<String, dynamic>
          : null;
      return null;
    }

    return _extractMessage(data, response.statusCode);
  }

  static Map<String, dynamic> _decode(String body) {
    try {
      final decoded = jsonDecode(body);
      if (decoded is Map<String, dynamic>) {
        return decoded;
      }
    } catch (_) {
      return {};
    }

    return {};
  }

  static String _extractMessage(Map<String, dynamic> data, int statusCode) {
    final message = data['message'];
    if (message is String && message.isNotEmpty) {
      return message;
    }

    final errors = data['errors'];
    if (errors is Map && errors.isNotEmpty) {
      final first = errors.values.first;
      if (first is List && first.isNotEmpty) {
        return first.first.toString();
      }
      return first.toString();
    }

    return 'تعذر الاتصال بالخادم. رمز الاستجابة: $statusCode';
  }
}
