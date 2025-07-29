<script>
    import { router, page } from '@inertiajs/svelte';
    import { onMount } from 'svelte';

    // Form data
    let form = {
        firstname: '',
        lastname: '',
        email: '',
        phone: '',
        grade: ''
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // Phone input element reference
    let phoneInput;
    
    // intlTelInput instance
    let itiInstance;

    // reCAPTCHA variables
    let recaptchaResponse = '';
    let recaptchaWidget = null;

    // Get current locale from page props
    $: currentLocale = $page.props.locale || 'en';

    // Translations
    const translations = {
        en: {
            title: 'Saud International Schools - Student Application',
            welcome: 'Welcome to Saud International Schools',
            description: 'We are proud to be a prestigious institution and embark on a journey of academic excellence and personal growth at Saud International Schools.',
            firstName: 'First Name',
            firstNamePlaceholder: 'Enter your first name',
            lastName: 'Last Name',
            lastNamePlaceholder: 'Enter your last name',
            email: 'Email',
            emailPlaceholder: 'email@example.com',
            phone: 'Phone Number',
            phonePlaceholder: 'Enter phone number',
            grade: 'Grade Level',
            gradePlaceholder: 'Select a grade',
            submit: 'Submit Application',
            submitting: 'Submitting Application...',
            success: 'Application submitted successfully'
        },
        ar: {
            title: 'مدارس سعود العالمية - طلب تسجيل الطالب',
            welcome: 'مرحباً بكم في مدارس سعود العالمية',
            description: 'نحن فخورون بكوننا مؤسسة تعليمية مرموقة ننطلق في رحلة التميز الأكاديمي والنمو الشخصي في مدارس سعود العالمية.',
            firstName: 'الاسم الأول',
            firstNamePlaceholder: 'أدخل اسمك الأول',
            lastName: 'اسم العائلة',
            lastNamePlaceholder: 'أدخل اسم العائلة',
            email: 'البريد الإلكتروني',
            emailPlaceholder: 'example@email.com',
            phone: 'رقم الهاتف',
            phonePlaceholder: 'أدخل رقم الهاتف',
            grade: 'المستوى الدراسي',
            gradePlaceholder: 'اختر المستوى الدراسي',
            submit: 'إرسال الطلب',
            submitting: 'جاري إرسال الطلب...',
            success: 'تم إرسال الطلب بنجاح'
        }
    };

    // Get translation function
    function t(key) {
        return translations[currentLocale][key] || translations.en[key] || key;
    }

    // Switch language function - calls backend route
    function switchLanguage(locale) {
        // Use window.location.href to navigate to the language switch route
        window.location.href = route('lang.switch', { locale: locale });
    }

    // Grade options with translations
    const gradeOptionsData = [
        { value: 'prek', en: 'Pre-K', ar: 'ما قبل الروضة' },
        { value: 'kg1', en: 'KG1', ar: 'الروضة الأولى' },
        { value: 'kg2', en: 'KG2', ar: 'الروضة الثانية' },
        { value: 'g1', en: 'Grade 1', ar: 'الصف الأول' },
        { value: 'g2', en: 'Grade 2', ar: 'الصف الثاني' },
        { value: 'g3', en: 'Grade 3', ar: 'الصف الثالث' },
        { value: 'g4', en: 'Grade 4', ar: 'الصف الرابع' },
        { value: 'g5', en: 'Grade 5', ar: 'الصف الخامس' },
        { value: 'g6', en: 'Grade 6', ar: 'الصف السادس' },
        { value: 'g7', en: 'Grade 7', ar: 'الصف السابع' },
        { value: 'g8', en: 'Grade 8', ar: 'الصف الثامن' },
        { value: 'g9', en: 'Grade 9', ar: 'الصف التاسع' },
        { value: 'g10', en: 'Grade 10', ar: 'الصف العاشر' },
        { value: 'g11', en: 'Grade 11', ar: 'الصف الحادي عشر' },
        { value: 'g12', en: 'Grade 12', ar: 'الصف الثاني عشر' }
    ];

    // Get localized grade options
    $: gradeOptions = gradeOptionsData.map(grade => ({
        value: grade.value,
        label: grade[currentLocale]
    }));

    // Set document attributes when locale changes
    $: if (currentLocale) {
        document.documentElement.setAttribute('lang', currentLocale);
        document.documentElement.setAttribute('dir', currentLocale === 'ar' ? 'rtl' : 'ltr');
    }

    // reCAPTCHA callback function
    function onRecaptchaCallback(response) {
        recaptchaResponse = response;
    }

    // Initialize reCAPTCHA
    function initRecaptcha() {
        if ($page.props.captcha?.enabled && window.grecaptcha && $page.props.captcha?.site_key) {
            try {
                recaptchaWidget = window.grecaptcha.render('recaptcha-container', {
                    'sitekey': $page.props.captcha.site_key,
                    'callback': onRecaptchaCallback,
                    'expired-callback': () => { recaptchaResponse = ''; },
                    'error-callback': () => { recaptchaResponse = ''; }
                });
            } catch (error) {
                console.error('Error initializing reCAPTCHA:', error);
            }
        }
    }

    // Handle form submission
    function handleSubmit() {
        loading = true;
        
        // Get the formatted international phone number from int-tel-input
        if (itiInstance) {
            const fullNumber = itiInstance.getNumber();
            if (fullNumber) {
                form.phone = fullNumber;
            }
        }
        
        // Create FormData for proper submission
        const formData = new FormData();
        
        // Add form fields
        Object.keys(form).forEach(key => {
            if (form[key] !== null && form[key] !== '') {
                formData.append(key, form[key]);
            }
        });
        
        // Add CSRF token explicitly
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }

        // Add reCAPTCHA response if enabled
        if ($page.props.captcha?.enabled) {
            formData.append('g-recaptcha-response', recaptchaResponse);
        }
        
        router.post(route('submissions.store'), formData, {
            onError: (err) => {
                errors = err;
                loading = false;
            },
            onSuccess: () => {
                // Reset form on success
                form = {
                    firstname: '',
                    lastname: '',
                    email: '',
                    phone: '',
                    grade: ''
                };
                errors = {};
                loading = false;
                
                // Reset the phone input
                if (itiInstance) {
                    itiInstance.setCountry('sa');
                    itiInstance.setNumber('');
                }

                // Reset reCAPTCHA
                if ($page.props.captcha?.enabled && window.grecaptcha && recaptchaWidget !== null) {
                    window.grecaptcha.reset(recaptchaWidget);
                    recaptchaResponse = '';
                }
            },
            onFinish: () => {
                loading = false;
            }
        });
    }

    onMount(() => {
        // Initialize int-tel-input after DOM is ready
        setTimeout(() => {
            if (phoneInput && window.intlTelInput) {
                itiInstance = window.intlTelInput(phoneInput, {
                    initialCountry: 'sa',
                    preferredCountries: ['sa', 'ae', 'kw', 'bh', 'om', 'qa'],
                    separateDialCode: true,
                    formatOnDisplay: true,
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/utils.js"
                });

                // Function to update the phone input value with formatted international number
                function updatePhoneValue() {
                    if (itiInstance && phoneInput.value.trim() !== '') {
                        try {
                            const fullPhoneNumber = itiInstance.getNumber();
                            if (fullPhoneNumber) {
                                form.phone = fullPhoneNumber;
                            }
                        } catch (error) {
                            console.log('Error getting phone number:', error);
                        }
                    }
                }

                // Update phone field when user finishes typing
                phoneInput.addEventListener('blur', function() {
                    updatePhoneValue();
                });

                // Update phone field when user changes input
                phoneInput.addEventListener('input', function() {
                    // Debounce the update to avoid too frequent calls
                    clearTimeout(phoneInput.updateTimeout);
                    phoneInput.updateTimeout = setTimeout(() => {
                        updatePhoneValue();
                    }, 500); // Wait 500ms after user stops typing
                });

                // Update phone field when country is changed
                phoneInput.addEventListener('countrychange', function() {
                    updatePhoneValue();
                });

                // Update phone field when user pastes content
                phoneInput.addEventListener('paste', function() {
                    setTimeout(() => {
                        updatePhoneValue();
                    }, 100); // delay to let paste complete
                                 });
             }
         }, 200);

         // Initialize reCAPTCHA after scripts are loaded
         if ($page.props.captcha?.enabled) {
             const checkRecaptcha = setInterval(() => {
                 if (window.grecaptcha && window.grecaptcha.render) {
                     initRecaptcha();
                     clearInterval(checkRecaptcha);
                 }
             }, 100);
         }
     });
</script>

<svelte:head>
    <title>{t('title')}</title>
    
    <!-- Include int-tel-input CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/css/intlTelInput.min.css">
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/intlTelInput.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/utils.js"></script>
    
    <!-- Include reCAPTCHA if enabled -->
    {#if $page.props.captcha?.enabled}
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    {/if}
    
    <!-- Page -->
    <style>
        .branded-bg {
            background-image: url('/assets/img/submissions-bg.jpg');
            position: relative;
        }
        .branded-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
            z-index: 1;
        }
        .branded-bg > div {
            position: relative;
            z-index: 2;
        }

        .iti__selected-dial-code{
            display: none;
        }

        body {
            background: #fefefe;
        }

        /* RTL Support */
        [dir="rtl"] .grid {
            direction: rtl;
        }
        
        [dir="rtl"] .kt-input {
            text-align: right;
        }
        
        [dir="rtl"] .kt-form-label {
            text-align: right;
        }

        /* Language Switcher */
        .language-switcher {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 100;
            padding: 20px;
        }

                 [dir="rtl"] .language-switcher {
             right: auto;
             left: 20px;
         }

         /* reCAPTCHA styling */
         #recaptcha-container {
             transform-origin: center;
         }

         /* Responsive reCAPTCHA */
         @media (max-width: 480px) {
             #recaptcha-container {
                 transform: scale(0.85);
                 transform-origin: center;
             }
         }

     </style>
</svelte:head>

<div class="grid lg:grid-cols-2 grow" style="height: 100vh;">
    <!-- Language Switcher -->
    <div class="language-switcher">
        {#if $page.props.locale == 'ar' }
        <button 
            class="kt-btn kt-btn-primary kt-btn-lg" 
            on:click={() => switchLanguage('en')}
        >
            English
        </button>
        {:else if $page.props.locale == 'en'}
        <button 
            class="kt-btn kt-btn-primary kt-btn-lg" 
            on:click={() => switchLanguage('ar')}
        >
            العربية
        </button>
        {/if}
    </div>

    <!-- Left Column - Application Form -->
    <div class="flex justify-center items-center p-8 lg:p-10 order-2 lg:order-1">
        <div class="kt-card max-w-[370px] w-full">
            <form on:submit|preventDefault={handleSubmit} class="kt-card-content flex flex-col gap-5 p-10">
                {#if $page.props.flash?.success}
                    <div class="kt-alert kt-alert-success-outline" style="background-color: #e6f7ff; border-color: #91d5ff;">
                        <div class="kt-alert-content">
                            <span class="kt-alert-text">{t('success')}</span>
                        </div>
                    </div>
                {/if}

                <!-- First Name Input -->
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="firstname">
                        {t('firstName')}
                    </label>
                    <input 
                        id="firstname"
                        name="firstname"
                        type="text" 
                        class="kt-input {errors.firstname ? 'kt-input-error' : ''}" 
                        placeholder={t('firstNamePlaceholder')}
                        bind:value={form.firstname}
                        disabled={loading}
                    />
                    {#if errors.firstname}
                        <span class="text-sm text-destructive">{errors.firstname}</span>
                    {/if}
                </div>

                <!-- Last Name Input -->
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="lastname">
                        {t('lastName')}
                    </label>
                    <input 
                        id="lastname"
                        name="lastname"
                        type="text" 
                        class="kt-input {errors.lastname ? 'kt-input-error' : ''}" 
                        placeholder={t('lastNamePlaceholder')}
                        bind:value={form.lastname}
                        disabled={loading}
                    />
                    {#if errors.lastname}
                        <span class="text-sm text-destructive">{errors.lastname}</span>
                    {/if}
                </div>

                <!-- Email Input -->
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="email">
                        {t('email')}
                    </label>
                    <input 
                        id="email"
                        name="email"
                        type="email" 
                        class="kt-input {errors.email ? 'kt-input-error' : ''}" 
                        placeholder={t('emailPlaceholder')}
                        bind:value={form.email}
                        disabled={loading}
                    />
                    {#if errors.email}
                        <span class="text-sm text-destructive">{errors.email}</span>
                    {/if}
                </div>

                <!-- Phone Input -->
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="phone">
                        {t('phone')}
                    </label>
                    <input 
                        id="phone"
                        name="phone"
                        type="tel" 
                        class="kt-input {errors.phone ? 'kt-input-error' : ''}" 
                        placeholder={t('phonePlaceholder')}
                        bind:this={phoneInput}
                        disabled={loading}
                    />
                    {#if errors.phone}
                        <span class="text-sm text-destructive">{errors.phone}</span>
                    {/if}
                </div>

                                 <!-- Grade Selection -->
                 <div class="flex flex-col gap-1">
                     <label class="kt-form-label font-normal text-mono" for="grade">
                         {t('grade')}
                     </label>
                     <select 
                         id="grade"
                         name="grade"
                         class="kt-input {errors.grade ? 'kt-input-error' : ''}" 
                         bind:value={form.grade}
                         disabled={loading}
                     >
                         <option value="">{t('gradePlaceholder')}</option>
                         {#each gradeOptions as option}
                             <option value={option.value}>{option.label}</option>
                         {/each}
                     </select>
                     {#if errors.grade}
                         <span class="text-sm text-destructive">{errors.grade}</span>
                     {/if}
                 </div>

                 <!-- reCAPTCHA -->
                 {#if $page.props.captcha?.enabled}
                     <div class="flex flex-col gap-1">
                         <div id="recaptcha-container" class="flex justify-center"></div>
                         {#if errors['g-recaptcha-response']}
                             <span class="text-sm text-destructive">{errors['g-recaptcha-response']}</span>
                         {/if}
                     </div>
                 {/if}

                 <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="kt-btn kt-btn-primary flex justify-center grow"
                    disabled={loading}
                >
                    {#if loading}
                        <i class="ki-outline ki-loading text-base animate-spin me-2"></i>
                        {t('submitting')}
                    {:else}
                        {t('submit')}
                    {/if}
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column - Branded Background -->
    <div class="lg:rounded-xl lg:border lg:border-border lg:m-5 order-1 lg:order-2 bg-top xxl:bg-center xl:bg-cover bg-no-repeat branded-bg">
        <div class="flex flex-col p-8 lg:p-16 gap-4">
            <a href="/">
                <img class="h-[91px] max-w-none" src="/assets/img/logo.png" alt="Saud International Schools" style="height: 91px;"/>
            </a>
            <div class="flex flex-col gap-6">
                <h3 class="text-2xl font-semibold text-white">
                    {t('welcome')}
                </h3>
                <div class="text-base font-medium text-white">
                    {t('description')}
                </div>
            </div>
        </div>
    </div>
</div> 