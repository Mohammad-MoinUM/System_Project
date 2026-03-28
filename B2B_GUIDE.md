# B2B Corporate Client System - Complete Guide

## 📋 Table of Contents
1. [System Overview](#system-overview)
2. [Getting Started](#getting-started)
3. [User Roles & Permissions](#user-roles--permissions)
4. [Feature Walkthrough](#feature-walkthrough)
5. [Technical Architecture](#technical-architecture)
6. [API Endpoints](#api-endpoints)
7. [Troubleshooting](#troubleshooting)

---

## System Overview

### What is the B2B Corporate System?
The B2B Corporate Client system allows organizations to register as corporate customers and manage their service requests across multiple branches with role-based staff management, approval workflows, invoicing, and comprehensive booking tracking.

### Key Features
✅ **Company Registration** - Register as a corporate client with automatic approval  
✅ **Branch Management** - Create and manage multiple business branches  
✅ **Staff Management** - Invite team members with role-based access control  
✅ **Service Requests** - Request services with approval workflows  
✅ **Invoice Tracking** - Generate and manage monthly invoices  
✅ **Booking History** - View and track all corporate service bookings  
✅ **Role-Based Access** - 5 distinct roles with granular permissions  

---

## Getting Started

### 1. Register as a Corporate Client

**URL:** `http://127.0.0.1:8000/corporate/register`

**Registration Form Fields:**
- **Company Name** *(required)* - Your organization's legal name
- **Email** *(required)* - Company contact email
- **Phone** *(required)* - Company phone number
- **Address** *(required)* - Physical business address
- **City** *(required)* - City name
- **Postal Code** *(required)* - Postal/ZIP code
- **Contact Person Name** *(required)* - Primary contact person
- **Company Registration Number** *(required)* - Government registration ID
- **Company Documents** *(optional)* - Upload registration certificates
- **Admin User Details:**
  - First Name *(required)*
  - Last Name *(required)*
  - Email *(required, must be unique)*
  - Phone *(required)*
  - Password *(required, min 8 characters)*

**What Happens After Registration:**
1. Company is **automatically approved** (no admin review required)
2. Admin user account is created and auto-logged in
3. User is added as **"admin" member** to the company
4. Redirected to **Corporate Dashboard**

### 2. Access the Corporate Dashboard

**After Login:** You will automatically be redirected to `/corporate` dashboard

**Dashboard Shows:**
- Company overview card with approval status (should show "Approved" ✅)
- Statistics: Total Branches, Staff Members, Pending Requests, Monthly Spend
- Action buttons (visible based on your role):
  - Manage Branches (Admin & Manager)
  - Manage Staff (Admin only)
  - View Requests (Admin, Manager, Requester, Approver)
  - View Invoices (Admin & Finance)
  - Booking History (All roles)

---

## User Roles & Permissions

### 5 Corporate Roles

| Role | Can Access | Main Responsibilities |
|------|-----------|----------------------|
| **Admin** | Everything | Full control - manage all aspects |
| **Manager** | Branches, Requests, Bookings | Oversee operations across branches |
| **Requester** | Requests, Bookings | Request services for company |
| **Approver** | Requests, Bookings | Approve service requests |
| **Finance** | Invoices, Bookings | Manage billing and payments |

### Role-Based Button Visibility

```
Dashboard Button         | Admin | Manager | Requester | Approver | Finance
------------------------|----|---------|-----------|----------|--------
Manage Branches         |  ✅  |    ✅   |     ❌    |    ❌    |   ❌
Manage Staff           |  ✅  |    ❌   |     ❌    |    ❌    |   ❌
View Requests          |  ✅  |    ✅   |     ✅    |    ✅    |   ❌
View Invoices          |  ✅  |    ❌   |     ❌    |    ❌    |   ✅
Booking History        |  ✅  |    ✅   |     ✅    |    ✅    |   ✅
```

### Permission Matrix

**Dashboard Access:**
- Only users with `role === 'customer'` AND active company membership can access corporate dashboard

**Branch Management:**
- **Create/Edit/Delete Branches:** Admin, Manager only
- **View All Branches:** Admin, Manager only

**Staff Management:**
- **Invite/Manage Staff:** Admin only
- **View Company Staff:** Admin, Manager

**Service Requests:**
- **Create Request:** Admin, Manager, Requester, Approver
- **Approve Request:** Admin, Approver
- **View All Requests:** Admin, Manager, Requester, Approver

**Invoices:**
- **View Invoices:** Admin, Finance
- **Generate Monthly Invoices:** Admin, Finance

---

## Feature Walkthrough

### 🏢 Managing Branches

**URL:** `/corporate/{companyId}/branches`

**What You Can Do:**
- ✅ View all branches
- ✅ Create new branch (requires Manager role)
- ✅ Assign branch manager
- ✅ Edit branch details
- ✅ Deactivate branch

**Required Fields to Create a Branch:**
- Branch Name
- Address
- City
- Branch Manager (optional, can assign later)
- Is Active (toggle)

**Example Use Case:**
*Your company has headquarters in Dhaka and needs to add a branch in Chittagong. Create the Chittagong branch, assign a manager, and track services across locations.*

---

### 👥 Managing Staff

**URL:** `/corporate/{companyId}/staff`

**What You Can Do:**
- ✅ View all staff members with their roles
- ✅ Invite new staff via email
- ✅ Assign specific roles (admin, manager, requester, approver, finance)
- ✅ Assign to specific branch
- ✅ Promote/change roles
- ✅ Deactivate staff member

**How to Invite Staff:**
1. Go to **Manage Staff** → **Invite New Member**
2. Enter:
   - Staff Member Email
   - Role (select from 5 available roles)
   - Branch (optional, assign to specific branch)
3. Click **Send Invitation**
4. Staff member receives email with joining link
5. Once they join, they gain access to assigned permissions

**Real-World Example:**
```
Admin invites:
- Sarah (manager@company.com) → Role: Manager → Branch: Dhaka HQ
- Ahmed (requester@company.com) → Role: Requester → Branch: Chittagong
- Fatima (finance@company.com) → Role: Finance → All Branches
```

---

### 📋 Service Requests

**URL:** `/corporate/{companyId}/requests`

**What You Can Do:**
- ✅ Create new service request
- ✅ View all pending/approved/rejected requests
- ✅ Approve requests (if you're Approver/Admin)
- ✅ Reject requests with reason
- ✅ Set expected completion date

**Creating a Service Request:**
1. Click **"View Requests"** on dashboard
2. Click **"Create New Request"**
3. Fill in:
   - Service Type (dropdown from available services)
   - Branch Location
   - Preferred Date
   - Notes/Special Requirements
   - Brief Description

**Example Request:**
```
Service Type: Office Cleaning
Branch: Chittagong Branch
Preferred Date: 2026-04-01
Notes: Deep cleaning required, focus on conference rooms
Status: Pending Approval
```

**Approval Workflow:**
```
Requester creates → System notifies Approver → Approver reviews → 
Approval status changes → Service provider is notified
```

---

### 💰 Invoices

**URL:** `/corporate/{companyId}/invoices`

**What You Can Do:**
- ✅ View all invoices (paid/pending)
- ✅ Filter invoices by status and month
- ✅ Generate monthly invoices
- ✅ Download invoice PDFs
- ✅ View invoice details and itemized breakdown

**Invoice Information Displayed:**
- Invoice Number (unique identifier)
- Billing Period (Month/Year)
- Total Amount (all services in period)
- Status (Paid/Pending/Overdue)
- Item Breakdown (service by service)
- Payment Due Date

**Example Invoice:**
```
Invoice #INV-2026-001
Period: March 2026
Total: ฿ 15,000.00

Items:
- Office Cleaning (3 sessions) ................. ฿ 3,000
- Plumbing Services (2 calls) ................. ฿ 4,500
- HVAC Maintenance (1 session) ................ ฿ 7,500

Status: Pending Payment
Due Date: 2026-04-10
```

---

### 🔖 Booking History

**URL:** `/corporate/{companyId}/bookings`

**What You Can Do:**
- ✅ View all company bookings
- ✅ Filter by status (Pending, Confirmed, Completed, Cancelled)
- ✅ Filter by branch
- ✅ View service provider details
- ✅ See booking timeline and status history
- ✅ View reviews and ratings

**Booking Details Include:**
- Booking ID
- Service Name & Type
- Service Provider Name & Contact
- Branch Location
- Booking Date
- Status Badge (color-coded)
- Service Provider Rating
- Cost

**Booking Status Flow:**
```
Pending → Confirmed → In Progress → Completed
                           ↓
                       Cancelled (optional)
```

---

## Technical Architecture

### Database Schema

#### 1. **companies** Table
```sql
- id (Primary Key)
- company_name: string
- email: string
- phone: string
- address: string
- city: string
- postal_code: string
- contact_person_name: string
- company_registration_number: string
- company_documents_path: string (file path)
- primary_admin_id: FK to users.id
- status: enum('approved', 'pending', 'rejected')
- approved_at: timestamp
- created_at, updated_at: timestamps
```

#### 2. **company_branches** Table
```sql
- id (Primary Key)
- company_id: FK to companies.id
- branch_name: string
- address: string
- city: string
- manager_id: FK to users.id (nullable)
- is_active: boolean
- created_at, updated_at: timestamps
```

#### 3. **company_user_memberships** Table
```sql
- id (Primary Key)
- company_id: FK to companies.id
- user_id: FK to users.id
- branch_id: FK to company_branches.id (nullable)
- role: enum('admin', 'manager', 'requester', 'approver', 'finance')
- is_active: boolean
- invited_at: timestamp (nullable)
- joined_at: timestamp
- created_at, updated_at: timestamps
```

#### 4. **company_service_requests** Table
```sql
- id (Primary Key)
- company_id: FK to companies.id
- branch_id: FK to company_branches.id
- service_id: FK to services.id
- requested_by: FK to users.id
- approved_by: FK to users.id (nullable)
- status: enum('pending', 'approved', 'rejected')
- rejection_reason: text (nullable)
- created_at, updated_at: timestamps
```

#### 5. **company_invoices** Table
```sql
- id (Primary Key)
- company_id: FK to companies.id
- invoice_number: string (unique)
- billing_period: string (e.g., "2026-03")
- total: decimal(10,2)
- status: enum('paid', 'pending', 'overdue')
- month: integer
- year: integer
- created_at, updated_at: timestamps
```

### Model Relationships

```
Company (1) ──┬─→ (Many) CompanyBranch
              ├─→ (Many) CompanyUserMembership
              ├─→ (Many) CompanyServiceRequest
              ├─→ (Many) CompanyInvoice
              └─→ (Many) Booking

User (1) ──┬─→ (Many) CompanyUserMembership
           ├─→ (Many) CompanyServiceRequest (as approver)
           └─→ (Many) CompanyServiceRequest (as requester)

CompanyBranch (1) ──┬─→ (Many) CompanyServiceRequest
                    └─→ (Many) Booking

CompanyUserMembership (Many) ── (1) Company
                              ── (1) User
                              ── (1) CompanyBranch (nullable)
```

### Controllers & Methods

**CorporateDashboardController:**
- `index()` - Display dashboard
- `bookingHistory()` - List all bookings
- `bookingDetails()` - Show single booking

**CompanyBranchController:**
- `index()` - List branches
- `create()` - Show create form
- `store()` - Save new branch
- `edit()` - Show edit form
- `update()` - Save changes
- `destroy()` - Delete branch

**CompanyStaffController:**
- `index()` - List staff
- `create()` - Show invite form
- `inviteStaff()` - Send email invitation
- `edit()` - Modify role/branch
- `update()` - Save changes
- `destroy()` - Remove staff

**CompanyServiceRequestController:**
- `index()` - List requests
- `create()` - Show create form
- `store()` - Create request
- `approve()` - Approve request
- `reject()` - Reject request

**CompanyInvoiceController:**
- `index()` - List invoices
- `show()` - Invoice details
- `download()` - Download PDF
- `generateMonthly()` - Create invoice for month

### Middleware

**EnsureCorporateAccess:** Checks
- User is authenticated
- User role is 'customer'
- User has at least one active company membership

**VerifyCompanyAccess:** Checks
- Company exists
- User belongs to that specific company

---

## API Endpoints

### Dashboard Routes
```
GET    /corporate                          Dashboard home
GET    /corporate/{companyId}/bookings     Booking history list
GET    /corporate/{companyId}/booking/{id} Booking details
```

### Branch Management
```
GET    /corporate/{companyId}/branches            List branches
GET    /corporate/{companyId}/branches/create     Create form
POST   /corporate/{companyId}/branches            Save branch
GET    /corporate/{companyId}/branches/{id}       Branch details
GET    /corporate/{companyId}/branches/{id}/edit  Edit form
PUT    /corporate/{companyId}/branches/{id}       Update branch
DELETE /corporate/{companyId}/branches/{id}       Delete branch
```

### Staff Management
```
GET    /corporate/{companyId}/staff              List staff
GET    /corporate/{companyId}/staff/invite       Invite form
POST   /corporate/{companyId}/staff/invite       Send invite
GET    /corporate/{companyId}/staff/{id}/edit    Edit form
PUT    /corporate/{companyId}/staff/{id}         Update role
DELETE /corporate/{companyId}/staff/{id}         Remove staff
```

### Service Requests
```
GET    /corporate/{companyId}/requests           List requests
GET    /corporate/{companyId}/requests/create    Create form
POST   /corporate/{companyId}/requests           Save request
GET    /corporate/{companyId}/requests/{id}      Request details
GET    /corporate/{companyId}/requests/{id}/approve   Approval form
POST   /corporate/{companyId}/requests/{id}/approve   Approve request
POST   /corporate/{companyId}/requests/{id}/reject    Reject request
```

### Invoices
```
GET    /corporate/{companyId}/invoices                List invoices
GET    /corporate/{companyId}/invoices/{id}          Invoice details
GET    /corporate/{companyId}/invoices/{id}/download Download PDF
POST   /corporate/{companyId}/invoices/generate/{month}/{year}  Generate monthly
```

---

## Troubleshooting

### Issue: "Access Denied 403" on Dashboard Buttons

**Cause:** Your user role doesn't have permission for that section

**Solution:**
- Check your company role (Admin, Manager, Requester, Approver, Finance)
- Admin users can access all features
- Other roles see only buttons they're authorized for
- Ask your company admin to change your role if needed

**Example:**
```
Role: Requester
✅ Can: View Requests, View Bookings
❌ Cannot: Manage Branches, Manage Staff, View Invoices
```

---

### Issue: "You are not part of any company" Error

**Cause:** Your user account isn't linked to a company

**Solution:**
1. Make sure you registered via `/corporate/register` (not regular `/register`)
2. If registered incorrectly, ask admin to invite you to company via Staff Invite
3. Accept the invitation email sent to your account

---

### Issue: Can't See Corporate Dashboard After Login

**Cause:** Login redirects to wrong dashboard

**Solution:**
- This was automatically fixed in latest update
- If still issues, access directly: `http://127.0.0.1:8000/corporate`
- System now auto-detects corporate membership and redirects properly

---

### Issue: Invitation Email Not Received

**Cause:** Email might be in spam or configuration issue

**Solution:**
1. Check spam/promotions folder
2. Verify email address is correct
3. Contact admin to resend invitation
4. Check system logs: `storage/logs/laravel.log`

---

### Issue: Can't Create Branch / Staff / Request

**Cause:** Missing required fields or insufficient permissions

**Solution:**
- Fill all required fields correctly
- Verify your role allows the action
- Try again or contact admin
- Check error message for specific field issues

---

## Quick Reference

### URLs by Feature
| Feature | URL |
|---------|-----|
| Register | `/corporate/register` |
| Dashboard | `/corporate` |
| Branches | `/corporate/{id}/branches` |
| Staff | `/corporate/{id}/staff` |
| Requests | `/corporate/{id}/requests` |
| Invoices | `/corporate/{id}/invoices` |
| Bookings | `/corporate/{id}/bookings` |

### Keyboard Shortcuts
- Docs: This file
- Help: Contact admin@company.com
- Support: See README.md

### Contact & Support
- **Email:** support@system.local
- **Documentation:** This file (B2B_GUIDE.md)
- **Admin Guide:** ADMIN_PANEL_GUIDE.md
- **System README:** README.md

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-03-27 | Initial B2B release - All features complete |
| 1.0.1 | 2026-03-27 | Auto-redirect fix, navbar updates, documentation |

---

**Last Updated:** March 27, 2026  
**Maintained By:** Development Team  
**Status:** ✅ Production Ready
