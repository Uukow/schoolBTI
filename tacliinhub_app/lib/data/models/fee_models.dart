/// Fee Models for TacliinHub
library;

class FeeInvoice {
  final int id;
  final int studentId;
  final String invoiceNo;
  final double totalAmount;
  final double paidAmount;
  final double dueAmount;
  final double discount;
  final String status;
  final String? dueDate;
  final String createdAt;

  FeeInvoice({
    required this.id,
    required this.studentId,
    required this.invoiceNo,
    required this.totalAmount,
    required this.paidAmount,
    required this.dueAmount,
    required this.discount,
    required this.status,
    this.dueDate,
    required this.createdAt,
  });

  factory FeeInvoice.fromJson(Map<String, dynamic> json) {
    return FeeInvoice(
      id: json['id'] ?? 0,
      studentId: json['student_id'] ?? 0,
      invoiceNo: json['invoice_no'] ?? '',
      totalAmount:
          double.tryParse(json['total_amount']?.toString() ?? '0') ?? 0.0,
      paidAmount:
          double.tryParse(json['paid_amount']?.toString() ?? '0') ?? 0.0,
      dueAmount: double.tryParse(json['due_amount']?.toString() ?? '0') ?? 0.0,
      discount: double.tryParse(json['discount']?.toString() ?? '0') ?? 0.0,
      status: json['status'] ?? 'Unpaid',
      dueDate: json['due_date'],
      createdAt: json['created_at'] ?? '',
    );
  }
}

class FeePayment {
  final int id;
  final int studentId;
  final int? invoiceId;
  final double amount;
  final String paymentMethod;
  final String? transactionId;
  final String paymentDate;
  final String? remarks;

  FeePayment({
    required this.id,
    required this.studentId,
    this.invoiceId,
    required this.amount,
    required this.paymentMethod,
    this.transactionId,
    required this.paymentDate,
    this.remarks,
  });

  factory FeePayment.fromJson(Map<String, dynamic> json) {
    return FeePayment(
      id: json['id'] ?? 0,
      studentId: json['student_id'] ?? 0,
      invoiceId: json['invoice_id'],
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      paymentMethod: json['payment_method'] ?? '',
      transactionId: json['transaction_id'],
      paymentDate: json['payment_date'] ?? '',
      remarks: json['remarks'],
    );
  }
}

class FeesSummary {
  final double totalFees;
  final double paidAmount;
  final double dueAmount;
  final double discountAmount;
  final int totalInvoices;
  final int paidInvoices;
  final int overdueInvoices;
  final List<FeeInvoice> recentInvoices;
  final List<FeePayment> recentPayments;

  FeesSummary({
    required this.totalFees,
    required this.paidAmount,
    required this.dueAmount,
    required this.discountAmount,
    required this.totalInvoices,
    required this.paidInvoices,
    required this.overdueInvoices,
    required this.recentInvoices,
    required this.recentPayments,
  });

  factory FeesSummary.fromJson(Map<String, dynamic> json) {
    return FeesSummary(
      totalFees: double.tryParse(json['total_fees']?.toString() ?? '0') ?? 0.0,
      paidAmount:
          double.tryParse(json['paid_amount']?.toString() ?? '0') ?? 0.0,
      dueAmount: double.tryParse(json['due_amount']?.toString() ?? '0') ?? 0.0,
      discountAmount:
          double.tryParse(json['discount_amount']?.toString() ?? '0') ?? 0.0,
      totalInvoices: json['total_invoices'] ?? 0,
      paidInvoices: json['paid_invoices'] ?? 0,
      overdueInvoices: json['overdue_invoices'] ?? 0,
      recentInvoices:
          (json['recent_invoices'] as List<dynamic>?)
              ?.map((i) => FeeInvoice.fromJson(i))
              .toList() ??
          [],
      recentPayments:
          (json['recent_payments'] as List<dynamic>?)
              ?.map((p) => FeePayment.fromJson(p))
              .toList() ??
          [],
    );
  }
}












