# 🗄️ Database Schema

## Overview

OpenGovPortal uses **PostgreSQL** with a focus on performance, security, and scalability. The schema supports multi-tenancy (departments), multi-language content, and role-based access control.

## Entity Relationship Diagram

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│     users       │     │    roles        │     │  permissions    │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │◄────┤ id (PK)         │     │ id (PK)         │
│ name            │     │ name            │     │ name            │
│ email           │     │ guard_name      │     │ guard_name      │
│ password        │     └─────────────────┘     └─────────────────┘
│ department_id   │            ▲                         ▲
│ is_active       │            │                         │
└─────────────────┘     ┌──────┴────────┐         ┌─────┴──────────┐
         ▲              │  role_user    │         │ role_permission│
         │              ├───────────────┤         ├────────────────┤
         │              │ user_id (FK)  │         │ role_id (FK)   │
         │              │ role_id (FK)  │         │ permission_id  │
         │              └───────────────┘         └────────────────┘
         │
         │              ┌─────────────────┐
         └─────────────►│  departments    │
                        ├─────────────────┤
                        │ id (PK)         │
                        │ name            │
                        │ code            │
                        │ description     │
                        │ is_active       │
                        └─────────────────┘
                                 │
         ┌───────────────────────┼───────────────────────┐
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  announcements  │    │    services     │    │   downloads     │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ id (PK)         │    │ id (PK)         │    │ id (PK)         │
│ department_id   │    │ department_id   │    │ department_id   │
│ title           │    │ title           │    │ title           │
│ slug            │    │ description     │    │ file_path       │
│ content         │    │ icon            │    │ file_size       │
│ locale          │    │ is_active       │    │ download_count  │
│ published_at    │    └─────────────────┘    └─────────────────┘
│ is_published    │
└─────────────────┘
```

## Core Tables

### 1. Users & Authentication

```sql
-- Users table
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    department_id BIGINT REFERENCES departments(id),
    phone VARCHAR(20),
    avatar VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Password reset tokens
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sessions (for security)
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL
);
```

### 2. Departments (Multi-tenancy)

```sql
CREATE TABLE departments (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    logo VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    website VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX idx_departments_code ON departments(code);
CREATE INDEX idx_departments_active ON departments(is_active);
```

### 3. Content Tables

#### Announcements

```sql
CREATE TABLE announcements (
    id BIGSERIAL PRIMARY KEY,
    department_id BIGINT REFERENCES departments(id) ON DELETE CASCADE,
    author_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    
    -- Content
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) UNIQUE NOT NULL,
    excerpt TEXT,
    content TEXT NOT NULL,
    locale VARCHAR(5) DEFAULT 'ms',
    
    -- Media
    featured_image VARCHAR(255),
    attachments JSONB DEFAULT '[]',
    
    -- Publication
    is_published BOOLEAN DEFAULT false,
    is_featured BOOLEAN DEFAULT false,
    published_at TIMESTAMP NULL,
    expired_at TIMESTAMP NULL,
    
    -- Stats
    view_count INT DEFAULT 0,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Indexes for performance
CREATE INDEX idx_announcements_dept ON announcements(department_id);
CREATE INDEX idx_announcements_locale ON announcements(locale);
CREATE INDEX idx_announcements_published ON announcements(is_published, published_at);
CREATE INDEX idx_announcements_slug ON announcements(slug);
CREATE INDEX idx_announcements_search ON announcements USING gin(to_tsvector('simple', title || ' ' || COALESCE(content, '')));
```

#### Services

```sql
CREATE TABLE services (
    id BIGSERIAL PRIMARY KEY,
    department_id BIGINT REFERENCES departments(id) ON DELETE CASCADE,
    
    -- Content
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    requirements TEXT,
    procedures TEXT,
    fees TEXT,
    timeline TEXT,
    locale VARCHAR(5) DEFAULT 'ms',
    
    -- Media
    icon VARCHAR(100),
    image VARCHAR(255),
    documents JSONB DEFAULT '[]',
    
    -- Settings
    is_online BOOLEAN DEFAULT false,
    form_url VARCHAR(500),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(20),
    
    -- Status
    is_active BOOLEAN DEFAULT true,
    sort_order INT DEFAULT 0,
    
    -- Stats
    view_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_services_dept ON services(department_id);
CREATE INDEX idx_services_active ON services(is_active);
```

#### Downloads

```sql
CREATE TABLE downloads (
    id BIGSERIAL PRIMARY KEY,
    department_id BIGINT REFERENCES departments(id) ON DELETE CASCADE,
    
    -- File info
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100),
    
    -- Category
    category VARCHAR(100),
    locale VARCHAR(5) DEFAULT 'ms',
    
    -- Stats
    download_count INT DEFAULT 0,
    
    -- Status
    is_active BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_downloads_dept ON downloads(department_id);
CREATE INDEX idx_downloads_category ON downloads(category);
```

### 4. RBAC Tables (Spatie Permission)

```sql
-- Roles
CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    department_id BIGINT REFERENCES departments(id) ON DELETE CASCADE,
    description TEXT,
    is_system BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (name, guard_name, department_id)
);

-- Permissions
CREATE TABLE permissions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    module VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (name, guard_name)
);

-- Role-User pivot
CREATE TABLE role_user (
    role_id BIGINT REFERENCES roles(id) ON DELETE CASCADE,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    PRIMARY KEY (role_id, user_id)
);

-- Permission-Role pivot
CREATE TABLE permission_role (
    permission_id BIGINT REFERENCES permissions(id) ON DELETE CASCADE,
    role_id BIGINT REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (permission_id, role_id)
);

-- Indexes
CREATE INDEX idx_roles_dept ON roles(department_id);
CREATE INDEX idx_permissions_module ON permissions(module);
```

### 5. Audit & Activity Logs

```sql
-- Activity logs
CREATE TABLE activity_logs (
    id BIGSERIAL PRIMARY KEY,
    log_name VARCHAR(100) DEFAULT 'default',
    description TEXT NOT NULL,
    subject_type VARCHAR(255),
    subject_id BIGINT,
    causer_type VARCHAR(255),
    causer_id BIGINT,
    properties JSONB DEFAULT '{}',
    event VARCHAR(50),
    batch_uuid UUID,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_activity_logs_subject ON activity_logs(subject_type, subject_id);
CREATE INDEX idx_activity_logs_causer ON activity_logs(causer_type, causer_id);
CREATE INDEX idx_activity_logs_name ON activity_logs(log_name);
CREATE INDEX idx_activity_logs_created ON activity_logs(created_at);

-- Failed login attempts
CREATE TABLE login_attempts (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    was_successful BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_login_attempts_ip ON login_attempts(ip_address);
CREATE INDEX idx_login_attempts_email ON login_attempts(email);
CREATE INDEX idx_login_attempts_time ON login_attempts(created_at);
```

## Materialized Views (For Performance)

### Department Stats

```sql
CREATE MATERIALIZED VIEW mv_department_stats AS
SELECT 
    d.id as department_id,
    d.name as department_name,
    d.code as department_code,
    COUNT(DISTINCT a.id) as announcement_count,
    COUNT(DISTINCT s.id) as service_count,
    COUNT(DISTINCT dl.id) as download_count,
    COUNT(DISTINCT u.id) as user_count
FROM departments d
LEFT JOIN announcements a ON a.department_id = d.id AND a.is_published = true
LEFT JOIN services s ON s.department_id = d.id AND s.is_active = true
LEFT JOIN downloads dl ON dl.department_id = d.id AND dl.is_active = true
LEFT JOIN users u ON u.department_id = d.id AND u.is_active = true
GROUP BY d.id, d.name, d.code;

CREATE UNIQUE INDEX idx_mv_dept_stats_id ON mv_department_stats(department_id);

-- Refresh every hour
REFRESH MATERIALIZED VIEW CONCURRENTLY mv_department_stats;
```

### Popular Content

```sql
CREATE MATERIALIZED VIEW mv_popular_content AS
SELECT 
    'announcement' as content_type,
    a.id as content_id,
    a.title,
    a.department_id,
    d.name as department_name,
    a.view_count,
    a.published_at
FROM announcements a
JOIN departments d ON d.id = a.department_id
WHERE a.is_published = true
    AND a.published_at >= NOW() - INTERVAL '30 days'

UNION ALL

SELECT 
    'service' as content_type,
    s.id as content_id,
    s.title,
    s.department_id,
    d.name as department_name,
    s.view_count,
    s.created_at as published_at
FROM services s
JOIN departments d ON d.id = s.department_id
WHERE s.is_active = true

ORDER BY view_count DESC;

CREATE INDEX idx_mv_popular_dept ON mv_popular_content(department_id);
```

## Seeding Data

### Default Departments

```sql
INSERT INTO departments (name, code, description, is_active, sort_order) VALUES
('Jabatan Perdana Menteri', 'JPM', 'Prime Minister Department', true, 1),
('Kementerian Kewangan', 'MOF', 'Ministry of Finance', true, 2),
('Kementerian Pendidikan', 'MOE', 'Ministry of Education', true, 3),
('Kementerian Kesihatan', 'MOH', 'Ministry of Health', true, 4),
('Kementerian Dalam Negeri', 'MOHA', 'Ministry of Home Affairs', true, 5);
```

### Default Roles

```sql
-- System roles (global)
INSERT INTO roles (name, guard_name, description, is_system) VALUES
('Super Admin', 'web', 'Full system access', true),
('System Admin', 'web', 'System administration', true);

-- Department roles (per department)
-- Note: These would be created programmatically for each department
-- department_id, name, description
```

### Default Permissions

```sql
INSERT INTO permissions (name, guard_name, module, description) VALUES
-- Announcements
('announcements.view', 'web', 'announcements', 'View announcements'),
('announcements.create', 'web', 'announcements', 'Create announcements'),
('announcements.edit', 'web', 'announcements', 'Edit announcements'),
('announcements.delete', 'web', 'announcements', 'Delete announcements'),
('announcements.publish', 'web', 'announcements', 'Publish announcements'),

-- Services
('services.view', 'web', 'services', 'View services'),
('services.create', 'web', 'services', 'Create services'),
('services.edit', 'web', 'services', 'Edit services'),
('services.delete', 'web', 'services', 'Delete services'),

-- Downloads
('downloads.view', 'web', 'downloads', 'View downloads'),
('downloads.create', 'web', 'downloads', 'Create downloads'),
('downloads.edit', 'web', 'downloads', 'Edit downloads'),
('downloads.delete', 'web', 'downloads', 'Delete downloads'),

-- Users
('users.view', 'web', 'users', 'View users'),
('users.create', 'web', 'users', 'Create users'),
('users.edit', 'web', 'users', 'Edit users'),
('users.delete', 'web', 'users', 'Delete users'),

-- Departments
('departments.view', 'web', 'departments', 'View departments'),
('departments.manage', 'web', 'departments', 'Manage departments');
```

## Migration Files Structure

```
database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2024_01_01_000001_create_departments_table.php
│   ├── 2024_01_01_000002_create_announcements_table.php
│   ├── 2024_01_01_000003_create_services_table.php
│   ├── 2024_01_01_000004_create_downloads_table.php
│   ├── 2024_01_01_000005_create_activity_logs_table.php
│   └── 2024_01_01_000006_create_login_attempts_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── DepartmentSeeder.php
    ├── RolePermissionSeeder.php
    └── UserSeeder.php
```

## Next Steps

- [Installation Guide](installation.md)
- [Caching Strategy](caching.md)
- [Security Configuration](security.md)
