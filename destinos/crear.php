<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_nombre = $_SESSION['user_nombre'] ?? 'Operador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holcim · Registrar Destino</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg: #f4f7fb;
            --panel: #ffffff;
            --panel-2: #f8fafc;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #0f3d5e;
            --primary-2: #1d4ed8;
            --accent: #00c896;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
        }

        body.dark-mode {
            --bg: #0a0f1c;
            --panel: #111827;
            --panel-2: #0f172a;
            --text: #e5e7eb;
            --muted: #94a3b8;
            --primary: #1e293b;
            --primary-2: #3b82f6;
            --accent: #22c55e;
            --border: #1f2937;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            overflow-x: hidden;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            transition: background 0.25s ease, color 0.25s ease;
        }

        a {
            text-decoration: none;
        }

        button,
        input,
        textarea {
            font-family: inherit;
        }

        .layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(180deg, #003b5c 0%, #0f2740 100%);
            color: #fff;
            padding: 24px 18px;
            position: sticky;
            top: 0;
            height: 100vh;
            border-right: 1px solid rgba(255,255,255,0.08);
            z-index: 1200;
        }

        .sidebar-overlay {
            display: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 8px 10px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 22px;
        }

        .brand img {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 14px;
            background: #fff;
            padding: 6px;
        }

        .brand h1 {
            font-size: 20px;
            line-height: 1.1;
            letter-spacing: 0.8px;
        }

        .brand span {
            display: block;
            color: #7dd3fc;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 4px;
        }

        .menu-group {
            margin-bottom: 20px;
        }

        .menu-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.4px;
            color: rgba(255,255,255,.55);
            margin: 12px 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #e2e8f0;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 14px;
            transition: .25s;
            margin-bottom: 6px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.10);
            color: #fff;
            transform: translateX(4px);
        }

        .nav-link i {
            width: 18px;
            text-align: center;
        }

        .content {
            padding: 26px;
            min-width: 0;
            padding-bottom: 100px;
        }

        .mobile-topbar {
            display: none;
        }

        .mobile-icon-btn,
        .mobile-menu-btn {
            border: none;
            background: linear-gradient(135deg, #0f3d5e, #1d4ed8);
            color: white;
            padding: 12px 14px;
            border-radius: 14px;
            font-weight: 700;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: var(--shadow);
            font-size: 14px;
            display: none;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .welcome-box {
            background: var(--panel);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: var(--radius-xl);
            padding: 24px 28px;
            flex: 1;
            min-width: 320px;
        }

        .welcome-box h2 {
            font-size: 28px;
            margin-bottom: 8px;
            color: var(--text);
            line-height: 1.2;
        }

        .welcome-box p {
            color: var(--muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .top-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .action-card {
            background: var(--panel);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: var(--radius-lg);
            padding: 14px 18px;
            min-width: 170px;
        }

        .action-card .label {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 6px;
        }

        .action-card .value {
            font-weight: 700;
            font-size: 15px;
            color: var(--text);
        }

        .theme-btn,
        .logout-btn {
            border: none;
            background: var(--panel);
            color: var(--text);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.25s ease;
        }

        .logout-btn {
            color: #fff;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: none;
        }

        .mobile-hero {
            display: none;
        }

        .hero-strip {
            display: grid;
            grid-template-columns: 1.35fr 1fr 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .hero-card {
            background: linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 18px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, var(--primary-2), var(--accent));
        }

        .hero-card.main {
            background: linear-gradient(135deg, rgba(15,61,94,0.98), rgba(29,78,216,0.95));
            color: white;
            border: none;
        }

        .hero-card.main::before {
            background: linear-gradient(180deg, #ffffff, rgba(255,255,255,0.3));
        }

        .hero-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--muted);
            margin-bottom: 8px;
            font-weight: 700;
        }

        .hero-card.main .hero-title,
        .hero-card.main .hero-value,
        .hero-card.main .hero-sub {
            color: white;
        }

        .hero-value {
            font-size: 30px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 6px;
            word-break: break-word;
        }

        .hero-sub {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-size: 14px;
            flex-wrap: wrap;
            margin-bottom: 24px;
            background: var(--panel);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: 18px;
            padding: 16px 18px;
        }

        .breadcrumb a {
            color: var(--primary-2);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .breadcrumb .separator {
            color: var(--muted);
        }

        .info-banner {
            display: grid;
            grid-template-columns: 58px 1fr auto;
            gap: 16px;
            align-items: center;
            margin-bottom: 24px;
            background: linear-gradient(135deg, rgba(15,61,94,0.06), rgba(0,200,150,0.06));
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 18px 20px;
            box-shadow: var(--shadow);
        }

        .info-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #0f3d5e, #1d4ed8);
            color: white;
            font-size: 24px;
        }

        .info-title {
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .info-text {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--panel);
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::after {
            content: '';
            position: absolute;
            right: -18px;
            bottom: -18px;
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: rgba(29, 78, 216, 0.08);
        }

        .kpi-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            position: relative;
            z-index: 2;
        }

        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(29, 78, 216, 0.10);
            color: var(--primary-2);
            font-size: 20px;
            flex-shrink: 0;
        }

        .kpi-badge {
            background: rgba(16, 185, 129, 0.10);
            color: var(--success);
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            position: relative;
            z-index: 2;
        }

        .kpi-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--muted);
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
        }

        .kpi-value {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
            color: var(--text);
        }

        .kpi-foot {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.5;
            position: relative;
            z-index: 2;
        }

        .section {
            margin-bottom: 24px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .section-header h3 {
            font-size: 20px;
            color: var(--text);
            line-height: 1.2;
            position: relative;
        }

        .section-header h3::after {
            content: '';
            display: block;
            width: 42px;
            height: 3px;
            border-radius: 999px;
            margin-top: 8px;
            background: linear-gradient(135deg, var(--accent), var(--primary-2));
        }

        .section-header p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .form-panel {
            background: var(--panel);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: 28px;
            padding: 28px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 2px;
        }

        .col-full {
            grid-column: 1 / -1;
        }

        label {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 10px;
            font-weight: 700;
            color: var(--text);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        label i {
            color: var(--accent);
            width: 16px;
            text-align: center;
        }

        .required-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.10);
            color: var(--danger);
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .left-icon,
        .input-wrap .right-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 15px;
        }

        .input-wrap .left-icon {
            left: 14px;
            pointer-events: none;
        }

        .input-wrap .right-icon {
            right: 14px;
            color: var(--primary-2);
            cursor: pointer;
        }

        input[type="text"],
        input[type="tel"],
        textarea {
            width: 100%;
            border: 1px solid var(--border);
            background: var(--panel-2);
            color: var(--text);
            border-radius: 14px;
            padding: 14px 16px 14px 42px;
            font-size: 14px;
            outline: none;
            transition: all 0.25s ease;
        }

        textarea {
            resize: vertical;
            min-height: 110px;
            line-height: 1.5;
        }

        input:focus,
        textarea:focus {
            border-color: var(--primary-2);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.10);
            background: var(--panel);
        }

        input::placeholder,
        textarea::placeholder {
            color: var(--muted);
        }

        .help-text {
            margin-top: 8px;
            font-size: 12px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
            line-height: 1.5;
        }

        .help-text i {
            color: var(--accent);
            font-size: 12px;
        }

        .field-error {
            margin-top: 8px;
            font-size: 12px;
            color: var(--danger);
            display: none;
            align-items: center;
            gap: 6px;
            line-height: 1.5;
        }

        .field-error.show {
            display: flex;
        }

        .input-invalid {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08) !important;
        }

        .form-actions {
            display: flex;
            gap: 14px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            padding: 14px 20px;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 14px;
            transition: all 0.25s ease;
            min-width: 180px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0f3d5e, #1d4ed8);
            color: white;
        }

        .btn-secondary {
            background: var(--panel-2);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn:hover,
        .theme-btn:hover,
        .logout-btn:hover {
            transform: translateY(-2px);
        }

        .footer {
            margin-top: 28px;
            color: var(--muted);
            text-align: center;
            font-size: 13px;
            line-height: 1.5;
        }

        .mobile-bottom-nav {
            display: none;
        }

        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 3000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }

        .toast {
            min-width: 320px;
            max-width: 380px;
            background: var(--panel);
            color: var(--text);
            border: 1px solid var(--border);
            border-left: 5px solid var(--primary-2);
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            transform: translateX(120%);
            opacity: 0;
            pointer-events: auto;
            transition: transform 0.35s ease, opacity 0.35s ease;
            backdrop-filter: blur(10px);
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.hide {
            transform: translateX(120%);
            opacity: 0;
        }

        .toast-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 16px;
            flex-shrink: 0;
            background: rgba(29, 78, 216, 0.10);
            color: var(--primary-2);
        }

        .toast-content {
            flex: 1;
            min-width: 0;
        }

        .toast-title {
            font-size: 14px;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 3px;
        }

        .toast-message {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.5;
        }

        .toast-close {
            background: transparent;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 14px;
            padding: 2px;
            flex-shrink: 0;
        }

        .toast.success {
            border-left-color: var(--success);
        }

        .toast.success .toast-icon {
            background: rgba(16, 185, 129, 0.12);
            color: var(--success);
        }

        .toast.warning {
            border-left-color: var(--warning);
        }

        .toast.warning .toast-icon {
            background: rgba(245, 158, 11, 0.12);
            color: var(--warning);
        }

        .toast.danger {
            border-left-color: var(--danger);
        }

        .toast.danger .toast-icon {
            background: rgba(239, 68, 68, 0.12);
            color: var(--danger);
        }

        .toast.info {
            border-left-color: var(--primary-2);
        }

        .toast.info .toast-icon {
            background: rgba(29, 78, 216, 0.10);
            color: var(--primary-2);
        }

        .confirm-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            z-index: 4000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        .confirm-modal.show {
            opacity: 1;
            pointer-events: auto;
        }

        .confirm-box {
            width: 100%;
            max-width: 460px;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 24px;
            transform: translateY(12px) scale(0.98);
            transition: transform 0.25s ease;
        }

        .confirm-modal.show .confirm-box {
            transform: translateY(0) scale(1);
        }

        .confirm-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: rgba(239, 68, 68, 0.10);
            color: var(--danger);
            display: grid;
            place-items: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .confirm-box h3 {
            font-size: 22px;
            margin-bottom: 8px;
            color: var(--text);
        }

        .confirm-box p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .confirm-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .confirm-actions button {
            flex: 1;
            min-width: 140px;
            border: none;
            padding: 13px 16px;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .confirm-cancel {
            background: var(--panel-2);
            color: var(--text);
            border: 1px solid var(--border) !important;
        }

        .confirm-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .confirm-actions button:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 1300px) {
            .hero-strip,
            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 285px;
                max-width: 85vw;
                height: 100vh;
                transition: left 0.3s ease;
                overflow-y: auto;
            }

            .sidebar.open {
                left: 0;
            }

            .sidebar-overlay {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.5);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s ease;
                z-index: 1100;
            }

            .sidebar-overlay.show {
                opacity: 1;
                pointer-events: auto;
            }

            .content {
                padding: 14px;
                padding-top: 88px;
                padding-bottom: 110px;
            }

            .mobile-topbar {
                display: flex;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                padding: 12px 14px;
                background: rgba(244, 247, 251, 0.88);
                backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(226, 232, 240, 0.8);
                justify-content: space-between;
                align-items: center;
                gap: 10px;
            }

            body.dark-mode .mobile-topbar {
                background: rgba(10, 15, 28, 0.88);
                border-bottom: 1px solid rgba(31, 41, 55, 0.8);
            }

            .mobile-topbar-left {
                display: flex;
                align-items: center;
                gap: 10px;
                min-width: 0;
            }

            .mobile-logo {
                width: 42px;
                height: 42px;
                border-radius: 12px;
                object-fit: cover;
                background: #fff;
                padding: 4px;
                flex-shrink: 0;
            }

            .mobile-brand-text {
                min-width: 0;
            }

            .mobile-brand-text strong {
                display: block;
                font-size: 14px;
                line-height: 1.2;
            }

            .mobile-brand-text span {
                display: block;
                font-size: 11px;
                color: var(--muted);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .mobile-topbar-actions {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .mobile-menu-btn,
            .mobile-icon-btn {
                display: inline-flex;
                width: 44px;
                height: 44px;
                padding: 0;
                border-radius: 12px;
                flex-shrink: 0;
            }

            .topbar {
                display: none;
            }

            .mobile-hero {
                display: block;
                background: linear-gradient(135deg, #0f3d5e, #1d4ed8);
                color: white;
                border-radius: 24px;
                padding: 20px;
                margin-bottom: 16px;
                box-shadow: var(--shadow);
            }

            .mobile-hero h2 {
                font-size: 22px;
                margin-bottom: 8px;
                line-height: 1.2;
            }

            .mobile-hero p {
                font-size: 13px;
                line-height: 1.5;
                opacity: 0.92;
                margin-bottom: 16px;
            }

            .mobile-hero-meta {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .mobile-hero-chip {
                background: rgba(255,255,255,0.14);
                border: 1px solid rgba(255,255,255,0.15);
                padding: 12px;
                border-radius: 14px;
            }

            .mobile-hero-chip .label {
                font-size: 11px;
                opacity: 0.82;
                margin-bottom: 4px;
            }

            .mobile-hero-chip .value {
                font-size: 14px;
                font-weight: 700;
            }

            .hero-strip,
            .kpi-grid,
            .form-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .info-banner {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .mobile-bottom-nav {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                position: fixed;
                left: 10px;
                right: 10px;
                bottom: 10px;
                z-index: 1000;
                background: rgba(255,255,255,0.92);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(226, 232, 240, 0.9);
                box-shadow: var(--shadow);
                border-radius: 20px;
                padding: 8px;
                gap: 6px;
            }

            body.dark-mode .mobile-bottom-nav {
                background: rgba(17, 24, 39, 0.92);
                border-color: rgba(31, 41, 55, 0.9);
            }

            .mobile-bottom-nav a {
                text-decoration: none;
                color: var(--muted);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 5px;
                padding: 8px 4px;
                border-radius: 14px;
                font-size: 10px;
                font-weight: 700;
                min-height: 58px;
            }

            .mobile-bottom-nav a i {
                font-size: 16px;
            }

            .mobile-bottom-nav a.active {
                background: linear-gradient(135deg, #0f3d5e, #1d4ed8);
                color: white;
            }

            .toast-container {
                top: 84px;
                right: 12px;
                left: 12px;
            }

            .toast {
                min-width: 0;
                max-width: 100%;
                width: 100%;
            }
        }

        @media (max-width: 640px) {
            .content {
                padding: 12px;
                padding-top: 84px;
                padding-bottom: 108px;
            }

            .mobile-hero {
                border-radius: 20px;
                padding: 18px;
            }

            .mobile-hero h2 {
                font-size: 20px;
            }

            .welcome-box,
            .hero-card,
            .form-panel,
            .kpi-card {
                border-radius: 18px;
            }

            .form-panel {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="mobile-topbar">
    <div class="mobile-topbar-left">
        <img src="../logo.jpg" alt="Holcim" class="mobile-logo" onerror="this.src='https://cdn.brandfetch.io/idZ5TRl0IY/w/1757/h/1757/theme/dark/icon.jpeg'">
        <div class="mobile-brand-text">
            <strong>HOLCIM</strong>
            <span>Nuevo destino</span>
        </div>
    </div>

    <div class="mobile-topbar-actions">
        <button class="mobile-icon-btn" id="themeToggleMobile" aria-label="Cambiar tema">
            <i class="fas fa-moon"></i>
        </button>
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</div>

<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="../logo.jpg" alt="Holcim" onerror="this.src='https://cdn.brandfetch.io/idZ5TRl0IY/w/1757/h/1757/theme/dark/icon.jpeg'">
            <div>
                <h1>HOLCIM</h1>
                <span>Sistema de gestión integral</span>
            </div>
        </div>

        <div class="menu-group">
            <div class="menu-title">Principal</div>
            <a class="nav-link" href="../index.php"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a class="nav-link" href="../produccion/listar.php"><i class="fas fa-industry"></i> Producción</a>
            <a class="nav-link" href="../despacho/listar.php"><i class="fas fa-truck"></i> Despacho</a>
            <a class="nav-link active" href="../destinos/listar.php"><i class="fas fa-map-location-dot"></i> Destinos</a>
        </div>

        <div class="menu-group">
            <div class="menu-title">Logística</div>
            <a class="nav-link" href="../camiones/listar.php"><i class="fas fa-truck-moving"></i> Camiones</a>
            <a class="nav-link" href="../materiales/listar.php"><i class="fas fa-cubes"></i> Materiales</a>
            <a class="nav-link" href="../cargas/listar.php"><i class="fas fa-weight-hanging"></i> Cargas</a>
        </div>

        <div class="menu-group">
            <div class="menu-title">Recursos</div>
            <a class="nav-link" href="../trabajadores/listar.php"><i class="fas fa-users"></i> Trabajadores</a>
            <a class="nav-link" href="../contactos/listar.php"><i class="fas fa-address-book"></i> Contactos</a>
            <a class="nav-link" href="../logout.php"><i class="fas fa-right-from-bracket"></i> Cerrar sesión</a>
        </div>
    </aside>

    <main class="content">
        <section class="mobile-hero">
            <h2>Registrar destino</h2>
            <p>Agrega un nuevo destino con datos de ubicación y contacto dentro de una vista moderna y consistente con el sistema.</p>
            <div class="mobile-hero-meta">
                <div class="mobile-hero-chip">
                    <div class="label">Fecha</div>
                    <div class="value"><?php echo date('d/m/Y'); ?></div>
                </div>
                <div class="mobile-hero-chip">
                    <div class="label">Módulo</div>
                    <div class="value">Destinos</div>
                </div>
            </div>
        </section>

        <div class="topbar">
            <div class="welcome-box">
                <h2>Alta de destino</h2>
                <p>Captura el nombre, ubicación y medios de contacto de un nuevo destino para fortalecer el control logístico del sistema.</p>
            </div>

            <div class="top-actions">
                <div class="action-card">
                    <div class="label">Usuario</div>
                    <div class="value"><?php echo htmlspecialchars($user_nombre); ?></div>
                </div>

                <div class="action-card">
                    <div class="label">Fecha actual</div>
                    <div class="value"><?php echo date('d/m/Y'); ?></div>
                </div>

                <button class="theme-btn" id="themeToggle">
                    <i class="fas fa-moon"></i>
                    <span id="themeText">Modo oscuro</span>
                </button>

                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>

        <section class="hero-strip">
            <div class="hero-card main">
                <div class="hero-title">Registro operativo</div>
                <div class="hero-value">Destino</div>
                <div class="hero-sub">Captura nombre, dirección, contacto y teléfono para integrar un nuevo punto de entrega o recepción.</div>
            </div>

            <div class="hero-card">
                <div class="hero-title">Campo principal</div>
                <div class="hero-value">Nombre</div>
                <div class="hero-sub">Identifica el destino dentro del sistema y facilita su uso en despachos.</div>
            </div>

            <div class="hero-card">
                <div class="hero-title">Referencia</div>
                <div class="hero-value">Ubicación</div>
                <div class="hero-sub">La dirección ayuda a mejorar el contexto logístico y la trazabilidad.</div>
            </div>

            <div class="hero-card">
                <div class="hero-title">Contacto</div>
                <div class="hero-value">Teléfono</div>
                <div class="hero-sub">Permite comunicación rápida con el responsable del destino cuando sea necesario.</div>
            </div>
        </section>

        <div class="breadcrumb">
            <a href="../index.php"><i class="fas fa-house"></i> Inicio</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <a href="listar.php"><i class="fas fa-map-location-dot"></i> Destinos</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <span><i class="fas fa-plus"></i> Nuevo destino</span>
        </div>

        <div class="info-banner">
            <div class="info-icon">
                <i class="fas fa-map-pin"></i>
            </div>
            <div>
                <div class="info-title">Registro de destino</div>
                <div class="info-text">Completa la información principal para integrar un nuevo destino al catálogo operativo del sistema.</div>
            </div>
            <div class="info-badge">
                <i class="fas fa-calendar-alt"></i>
                <?php echo date('d/m/Y'); ?>
            </div>
        </div>

        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="fas fa-building"></i></div>
                    <div class="kpi-badge">Base</div>
                </div>
                <div class="kpi-label">Campo clave</div>
                <div class="kpi-value">Nombre</div>
                <div class="kpi-foot">Dato obligatorio para identificar el destino dentro del catálogo logístico.</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="fas fa-road"></i></div>
                    <div class="kpi-badge">Ubicación</div>
                </div>
                <div class="kpi-label">Dirección</div>
                <div class="kpi-value">Mapa</div>
                <div class="kpi-foot">Permite documentar con mayor claridad el punto de entrega o recepción.</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
                    <div class="kpi-badge">Responsable</div>
                </div>
                <div class="kpi-label">Contacto</div>
                <div class="kpi-value">Persona</div>
                <div class="kpi-foot">Nombre del encargado o responsable operativo del destino.</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="fas fa-phone"></i></div>
                    <div class="kpi-badge">Comunicación</div>
                </div>
                <div class="kpi-label">Teléfono</div>
                <div class="kpi-value">Directo</div>
                <div class="kpi-foot">Facilita el contacto rápido ante dudas, entregas o incidencias.</div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <div>
                    <h3>Formulario de registro</h3>
                    <p>Completa los datos obligatorios y opcionales para guardar un nuevo destino en el sistema.</p>
                </div>
            </div>

            <div class="form-panel">
                <form action="guardar.php" method="POST" id="destinoForm" novalidate>
                    <div class="form-grid">
                        <div class="form-group col-full">
                            <label for="nombre">
                                <i class="fas fa-building"></i>
                                Nombre del destino
                                <span class="required-badge">Obligatorio</span>
                            </label>
                            <div class="input-wrap">
                                <i class="fas fa-map-marker-alt left-icon"></i>
                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    required
                                    maxlength="100"
                                    placeholder="Ej: Obra Central, Planta Norte, Cliente Industrial..."
                                    autocomplete="off"
                                >
                            </div>
                            <div class="help-text">
                                <i class="fas fa-circle-info"></i>
                                Nombre identificador del destino dentro del sistema.
                            </div>
                            <div class="field-error" id="error-nombre">
                                <i class="fas fa-circle-exclamation"></i>
                                El nombre del destino es obligatorio y debe tener al menos 3 caracteres.
                            </div>
                        </div>

                        <div class="form-group col-full">
                            <label for="direccion">
                                <i class="fas fa-road"></i>
                                Dirección
                            </label>
                            <div class="input-wrap">
                                <i class="fas fa-location-dot left-icon" style="top: 18px; transform:none;"></i>
                                <textarea
                                    id="direccion"
                                    name="direccion"
                                    placeholder="Dirección completa del destino: calle, número, colonia, ciudad, estado..."
                                ></textarea>
                            </div>
                            <div class="help-text">
                                <i class="fas fa-circle-info"></i>
                                Campo opcional para referencia logística más precisa.
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contacto">
                                <i class="fas fa-user-tie"></i>
                                Nombre de contacto
                            </label>
                            <div class="input-wrap">
                                <i class="fas fa-user left-icon"></i>
                                <input
                                    type="text"
                                    id="contacto"
                                    name="contacto"
                                    maxlength="100"
                                    placeholder="Nombre del responsable en el destino"
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="telefono">
                                <i class="fas fa-phone"></i>
                                Teléfono
                            </label>
                            <div class="input-wrap">
                                <i class="fas fa-phone-alt left-icon"></i>
                                <input
                                    type="tel"
                                    id="telefono"
                                    name="telefono"
                                    maxlength="20"
                                    placeholder="Ej: 55-1234-5678"
                                >
                                <i class="fas fa-copy right-icon" id="copyPhoneBtn" title="Copiar teléfono"></i>
                            </div>
                            <div class="help-text">
                                <i class="fas fa-circle-info"></i>
                                Campo opcional. Se formatea automáticamente mientras escribes.
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Guardar destino
                        </button>

                        <button type="button" class="btn btn-secondary" id="cancelBtn">
                            <i class="fas fa-xmark"></i>
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <div class="footer">
            © 2026 Holcim México · Sistema de Gestión de Cemento · Módulo de Destinos v3.0
        </div>
    </main>
</div>

<nav class="mobile-bottom-nav">
    <a href="../index.php">
        <i class="fas fa-house"></i>
        <span>Inicio</span>
    </a>
    <a href="../produccion/listar.php">
        <i class="fas fa-industry"></i>
        <span>Producción</span>
    </a>
    <a href="../despacho/listar.php">
        <i class="fas fa-truck"></i>
        <span>Despacho</span>
    </a>
    <a href="listar.php" class="active">
        <i class="fas fa-location-dot"></i>
        <span>Destinos</span>
    </a>
    <a href="../logout.php">
        <i class="fas fa-right-from-bracket"></i>
        <span>Salir</span>
    </a>
</nav>

<div class="toast-container" id="toastContainer"></div>

<div class="confirm-modal" id="confirmModal">
    <div class="confirm-box">
        <div class="confirm-icon">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h3>Cancelar registro</h3>
        <p>¿Estás seguro de salir? Los datos no guardados del formulario se perderán.</p>
        <div class="confirm-actions">
            <button type="button" class="confirm-cancel" id="cancelConfirmBtn">Seguir editando</button>
            <button type="button" class="confirm-delete" id="acceptConfirmBtn">Sí, cancelar</button>
        </div>
    </div>
</div>

<script>
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    const themeToggleMobile = document.getElementById('themeToggleMobile');

    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
    } else if (savedTheme === 'light') {
        body.classList.remove('dark-mode');
    } else if (prefersDark) {
        body.classList.add('dark-mode');
    }

    function updateThemeUI() {
        const isDark = body.classList.contains('dark-mode');

        if (themeToggle) {
            themeToggle.innerHTML = isDark
                ? '<i class="fas fa-sun"></i><span id="themeText">Modo claro</span>'
                : '<i class="fas fa-moon"></i><span id="themeText">Modo oscuro</span>';
        }

        if (themeToggleMobile) {
            themeToggleMobile.innerHTML = isDark
                ? '<i class="fas fa-sun"></i>'
                : '<i class="fas fa-moon"></i>';
            themeToggleMobile.setAttribute('aria-label', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
        }
    }

    function toggleTheme() {
        body.classList.toggle('dark-mode');
        const isDark = body.classList.contains('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeUI();

        showToast(
            isDark ? 'Modo oscuro activado' : 'Modo claro activado',
            isDark ? 'La interfaz se ajustó al tema oscuro.' : 'La interfaz se ajustó al tema claro.',
            'info',
            2500
        );
    }

    updateThemeUI();

    if (themeToggle) themeToggle.addEventListener('click', toggleTheme);
    if (themeToggleMobile) themeToggleMobile.addEventListener('click', toggleTheme);

    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function closeSidebar() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('show');
    }

    function openSidebar() {
        sidebar.classList.add('open');
        sidebarOverlay.classList.add('show');
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
            closeSidebar();
        }
    });

    const toastContainer = document.getElementById('toastContainer');

    function getToastIcon(type) {
        switch (type) {
            case 'success': return 'fas fa-circle-check';
            case 'warning': return 'fas fa-triangle-exclamation';
            case 'danger': return 'fas fa-circle-xmark';
            default: return 'fas fa-circle-info';
        }
    }

    function showToast(title, message = '', type = 'info', duration = 4000) {
        if (!toastContainer) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="${getToastIcon(type)}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" aria-label="Cerrar notificación">
                <i class="fas fa-xmark"></i>
            </button>
        `;

        toastContainer.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        const closeBtn = toast.querySelector('.toast-close');

        function removeToast() {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 350);
        }

        closeBtn.addEventListener('click', removeToast);
        setTimeout(removeToast, duration);
    }

    const destinoForm = document.getElementById('destinoForm');
    const nombreInput = document.getElementById('nombre');
    const telefonoInput = document.getElementById('telefono');
    const errorNombre = document.getElementById('error-nombre');

    function setError(input, errorElement, show) {
        if (!input || !errorElement) return;
        input.classList.toggle('input-invalid', show);
        errorElement.classList.toggle('show', show);
    }

    if (nombreInput) {
        nombreInput.addEventListener('input', () => {
            setError(nombreInput, errorNombre, false);
        });

        nombreInput.addEventListener('blur', function () {
            this.value = this.value.trim();
        });
    }

    if (telefonoInput) {
        telefonoInput.addEventListener('input', function () {
            let value = this.value.replace(/\D/g, '');

            if (value.length > 10) {
                value = value.substring(0, 10);
            }

            if (value.length > 6) {
                this.value = value.substring(0, 2) + '-' + value.substring(2, 6) + '-' + value.substring(6);
            } else if (value.length > 2) {
                this.value = value.substring(0, 2) + '-' + value.substring(2);
            } else {
                this.value = value;
            }
        });
    }

    function validarFormulario() {
        let valido = true;
        const nombre = nombreInput.value.trim();

        if (nombre.length < 3) {
            setError(nombreInput, errorNombre, true);
            nombreInput.focus();
            valido = false;
        } else {
            setError(nombreInput, errorNombre, false);
        }

        return valido;
    }

    if (destinoForm) {
        destinoForm.addEventListener('submit', function (e) {
            if (!validarFormulario()) {
                e.preventDefault();
                showToast(
                    'Formulario incompleto',
                    'El nombre del destino debe tener al menos 3 caracteres.',
                    'warning',
                    3200
                );
                return;
            }

            showToast(
                'Validación correcta',
                'Se enviará el registro del nuevo destino.',
                'success',
                1800
            );
        });
    }

    const copyPhoneBtn = document.getElementById('copyPhoneBtn');

    async function copyPhoneValue() {
        const value = telefonoInput.value.trim();

        if (!value) {
            showToast(
                'Sin teléfono',
                'Primero escribe un número para poder copiarlo.',
                'warning',
                2500
            );
            return;
        }

        try {
            await navigator.clipboard.writeText(value);
            const originalClass = copyPhoneBtn.className;
            copyPhoneBtn.className = 'fas fa-check right-icon';
            copyPhoneBtn.style.color = 'var(--success)';

            showToast(
                'Teléfono copiado',
                'El número fue copiado al portapapeles.',
                'success',
                2200
            );

            setTimeout(() => {
                copyPhoneBtn.className = originalClass;
                copyPhoneBtn.style.color = '';
            }, 1800);
        } catch (error) {
            showToast(
                'No se pudo copiar',
                'Tu navegador no permitió copiar el contenido.',
                'danger',
                2500
            );
        }
    }

    if (copyPhoneBtn) {
        copyPhoneBtn.addEventListener('click', copyPhoneValue);
    }

    const cancelBtn = document.getElementById('cancelBtn');
    const confirmModal = document.getElementById('confirmModal');
    const cancelConfirmBtn = document.getElementById('cancelConfirmBtn');
    const acceptConfirmBtn = document.getElementById('acceptConfirmBtn');

    function abrirConfirmacionCancelacion() {
        if (confirmModal) confirmModal.classList.add('show');
    }

    function cerrarConfirmacionCancelacion() {
        if (confirmModal) confirmModal.classList.remove('show');
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', abrirConfirmacionCancelacion);
    }

    if (cancelConfirmBtn) {
        cancelConfirmBtn.addEventListener('click', cerrarConfirmacionCancelacion);
    }

    if (acceptConfirmBtn) {
        acceptConfirmBtn.addEventListener('click', () => {
            window.location.href = 'listar.php';
        });
    }

    if (confirmModal) {
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                cerrarConfirmacionCancelacion();
            }
        });
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarConfirmacionCancelacion();
            closeSidebar();
        }
    });

    window.addEventListener('load', () => {
        showToast(
            'Formulario listo',
            'Completa los datos del destino para integrarlo al catálogo del sistema.',
            'info',
            3200
        );
    });
</script>
</body>
</html>