<x-layouts.app>
    <style>
        :root {
            --color-primary: #4f46e5; /* indigo-600 */
            --color-primary-light: #6366f1; /* indigo-500 */
            --color-accent: #dc2626; /* red-600 */
            --color-surface: #f4f4f5; /* zinc-100 */
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --color-surface: #27272a;
            }
        }

        .error-container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
        }

        .error-visual {
            margin-bottom: 48px;
            position: relative;
        }

        .error-code {
            font-size: 180px;
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, var(--color-accent), #f87171);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        .error-illustration {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
            z-index: 1;
            opacity: 0.1;
        }

        .error-content {
            background: var(--color-surface);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            padding: 48px 32px;
            margin-bottom: 32px;
        }

        @media (prefers-color-scheme: dark) {
            .error-content {
                border-color: rgba(255, 255, 255, 0.08);
            }
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .error-message {
            font-size: 16px;
            color: #71717a;
            line-height: 1.6;
            margin-bottom: 0;
        }

        @media (prefers-color-scheme: dark) {
            .error-message {
                color: #a1a1aa;
            }
        }

        .error-actions {
            display: flex;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--color-primary-light);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(79, 70, 229, 0.2);
        }

        @media (max-width: 480px) {
            .error-code {
                font-size: 120px;
            }

            .error-content {
                padding: 32px 24px;
            }

            h1 {
                font-size: 24px;
            }

            .error-message {
                font-size: 14px;
            }
        }
    </style>

    <div class="error-container">
        <div class="error-visual">
            <!-- Decorative background with lock icon concept -->
            <svg class="error-illustration" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg">
                <circle cx="150" cy="150" r="140" fill="none" stroke="currentColor" stroke-width="2" opacity="0.5"/>
                <circle cx="150" cy="150" r="100" fill="none" stroke="currentColor" stroke-width="2" opacity="0.3"/>
                <rect x="100" y="120" width="100" height="80" rx="8" fill="none" stroke="currentColor" stroke-width="2" opacity="0.2"/>
                <circle cx="150" cy="135" r="8" fill="none" stroke="currentColor" stroke-width="2" opacity="0.2"/>
            </svg>
            <h2 class="error-code">403</h2>
        </div>

        <div class="error-content">
            <h1>Access Forbidden</h1>
            <p class="error-message">
                You don't have permission to access this resource. If you believe this is an error, please contact your administrator for assistance.
            </p>
        </div>

        <div class="error-actions">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</x-layouts.app>
