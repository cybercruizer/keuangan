# Project Plan: Sistem Informasi Keuangan SMK Muhammadiyah Mungkid

## 1. Overview
This project implements a financial information system for **SMK Muhammadiyah Mungkid**. The system will manage academic years, classes (rombel), majors, students, billing, payments, reporting, and school settings. It will be built with **Laravel 13+Boost**, **PHP 8.4**, **Bootstrap (flat design)**, and **pastel color palette**.

---

## 2. Goals
- Provide a web‑based interface for administrators, tellers, headmaster, and students.
- Automate generation of billing, discounts, and receipts.
- Offer real‑time dashboards and detailed financial reports.
- Keep the codebase clean, test‑driven, and ready for Laravel Cloud deployment.

---

## 3. Functional Requirements
| Module | Features |
|--------|----------|
| **Manajemen** | • Academic Year (`tahun ajaran`)<br>• Class (Rombel) & Major (`jurusan`)<br>• Student data with categories: regular, 50% scholarship, 100% scholarship<br>• User roles: Admin, Teller, Headmaster (`kepsek`), Student (`murid`) |
| **Tagihan** | • Define Bill Types with nominal amount (e.g., SPP monthly, custom bills, savings)<br>• Checkbox for *discountable* bills<br>• Apply bill types to specific Rombel(s) and/or student categories<br>• Discount rules per student category with configurable percentages (50%/100%) | 
| **Dashboard** | • Daily, weekly, monthly payment charts<br>• Progress bar of payments per Rombel |
| **Pembayaran** | • Payment page with receipt printing (A5 landscape) |
| **Laporan** | • Cash flow reports (daily, monthly, yearly)<br>• Payment recap per student, per Rombel, per bill type |
| **Pengaturan** | • School identity (name, headmaster, logo, etc.) |

---

## 4. Non‑Functional Requirements
- **Technology Stack**: Laravel 13+Boost, PHP 8.4, Composer, Node.js 25.9, Bootstrap (flat design).
- **UI/UX**: Flat design with pastel primary and accent colors.
- **Testing**: Feature tests for each module using Laravel’s testing utilities.
- **Deployment**: Ready for Laravel Cloud (use `deploying-to-cloud` skill).
- **Performance**: Use eager loading, query caching where appropriate; dashboard charts generated via optimized queries.

---

## 5. High‑Level Architecture
```
Laravel (MVC)
│
├─ Controllers (Admin, Teller, Kepala, Murid)
├─ Models (AcademicYear, Class, Major, Student, User, BillType, Bill, Discount, Payment, CashFlow, Setting)
├─ Services (BillingService, DiscountService, ReportService)
├─ Views (Blade + Bootstrap)
└─ Routes (resource routes, named routes)
```

### Database Overview (simplified)
- `academic_years` (id, name, start_date, end_date)
- `classes` (id, name, major_id, academic_year_id)
- `majors` (id, name)
- `students` (id, nis, name, class_id, category ENUM[regular, scholarship_50, scholarship_100])
- `users` (id, name, email, password, role ENUM[admin, teller, kepsek, murid])
- `bill_types` (id, name, amount, is_discountable, frequency ENUM[monthly, one_time])
- `bill_assignments` (id, bill_type_id, class_id nullable, student_category nullable)
- `discounts` (id, student_category, bill_type_id, percent)
- `payments` (id, student_id, bill_type_id, amount_paid, payment_date, receipt_path)
- `cash_flows` (id, date, description, amount, type ENUM[income, expense])
- `settings` (key, value)

---

## 6. Milestones & Timeline (estimated)
| Phase | Description | Duration |
|-------|-------------|----------|
| **0** | Project bootstrap – repo init, Laravel 13+Boost install, CI setup | 1 week |
| **1** | Database schema & migrations (all tables above) | 1 week |
| **2** | Core models, factories, seeders | 1 week |
| **3** | Management module (academic years, classes, majors, students, user roles) | 2 weeks |
| **4** | Billing module – bill types, assignments, discount rules | 2 weeks |
| **5** | Payment workflow & receipt generation (A5 landscape) | 2 weeks |
| **6** | Dashboard charts & progress bars | 1 week |
| **7** | Reporting module (cash flow, payment recaps) | 1 week |
| **8** | Settings page (school identity) | 1 week |
| **9** | Front‑end polishing – Bootstrap flat design, pastel palette | 1 week |
| **10**| Testing – feature tests for each endpoint, UI tests | 1 week |
| **11**| Deployment preparation – Laravel Cloud, environment config | 1 week |
| **12**| Final QA & Documentation | 1 week |

_Total estimated time: ~14 weeks (≈3.5 months)_.

---

## 7. Testing Strategy
- **Unit tests** for models & services (e.g., discount calculation).
- **Feature tests** covering CRUD for management, billing creation, payment process, and report generation.
- Use Laravel factories for test data; leverage `testing-best-practices` skill for naming conventions.

---

## 8. Deployment Notes
- Run `php artisan migrate --seed` after provisioning the environment.
- Build assets with `npm run build` before deploying.
- For Laravel Cloud, activate the `deploying-to-cloud` skill and follow the standard CI/CD pipeline.

---

## 9. Open Decisions
- Whether to implement recurring payment automation (cron) for monthly SPP.
- Choice of charting library (Chart.js vs. Laravel‑Livewire + ApexCharts).
- Storage location for generated PDF receipts (local storage vs. cloud bucket).

Please review the plan and let me know if any sections need adjustment or additional detail.