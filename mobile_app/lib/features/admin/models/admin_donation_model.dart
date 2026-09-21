class AdminDonation {
  final int id;
  final String name;
  final String contact;
  final String? email;
  final String? panNumber;
  final double amount;
  final String paymentGateway;
  final String? gatewayOrderId;
  final String? gatewayPaymentId;
  final String paymentStatus;
  final String cause;
  final String? receiptUrl;
  final String? createdAt;

  const AdminDonation({
    required this.id,
    required this.name,
    required this.contact,
    this.email,
    this.panNumber,
    required this.amount,
    required this.paymentGateway,
    this.gatewayOrderId,
    this.gatewayPaymentId,
    required this.paymentStatus,
    required this.cause,
    this.receiptUrl,
    this.createdAt,
  });

  factory AdminDonation.fromJson(Map<String, dynamic> json) {
    return AdminDonation(
      id: json['id'] as int,
      name: json['name'] as String? ?? 'Devotee',
      contact: json['contact'] as String? ?? '',
      email: json['email'] as String?,
      panNumber: json['pan_number'] as String?,
      amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
      paymentGateway: json['payment_gateway'] as String? ?? 'razorpay',
      gatewayOrderId: json['gateway_order_id'] as String?,
      gatewayPaymentId: json['gateway_payment_id'] as String?,
      paymentStatus: json['payment_status'] as String? ?? 'PENDING',
      cause: json['cause'] as String? ?? 'General Fund',
      receiptUrl: json['receipt_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
