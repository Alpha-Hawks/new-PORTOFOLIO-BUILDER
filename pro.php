<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Portfolio & iOS Calendar</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { iosBlue: '#007AFF' }
                }
            }
        }
    </script>
    <style>
        body {
            background: radial-gradient(circle at top center, #1e293b, #020617);
            font-family: 'Inter', sans-serif;
            color: white;
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        /* Unified Glassmorphism */
        .glass {
            background: rgba(15, 23, 42, 0.85); /* Darker iOS base from Program 2 */
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        
        .input-glass {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .input-glass:focus, .input-glass.active-glass {
            border-color: #007AFF;
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.15);
            outline: none;
        }

        /* --- INTEGRATED CALENDAR STYLES (From Program 2) --- */
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
        .day-cell {
            aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
            border-radius: 10px; cursor: pointer; transition: all 0.2s ease; font-size: 0.85rem;
        }
        .day-cell:hover:not(.selected):not(.empty) { background: rgba(255, 255, 255, 0.1); }
        .day-cell.selected {
            background: #007AFF; color: white; font-weight: 600;
            box-shadow: 0 0 15px rgba(0, 122, 255, 0.5); transform: scale(1.05);
        }
        .day-cell.today { border: 1px solid #007AFF; color: #007AFF; }
        .picker-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }
        .picker-scroll::-webkit-scrollbar { width: 4px; }
        .picker-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .nav-btn:active { transform: scale(0.9); transition: 0.1s; }
        /* ------------------------------------------------ */

        .form-fade-out {
            opacity: 0;
            transform: scale(0.95);
            filter: blur(10px);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }

        #portfolioView { display: none; }
        .portfolio-pre-anim { opacity: 0; filter: blur(40px); transform: scale(0.85); }
        .portfolio-focus-in {
            opacity: 1; filter: blur(0px); transform: scale(1);
            transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
    </style>
</head>
<body class="p-4 md:p-10 flex items-center justify-center">

    <div id="formView" class="glass w-full max-w-5xl rounded-[2.5rem] p-8 md:p-12 transition-all">
        <header class="mb-10 text-center">
            <h1 class="text-4xl font-extrabold tracking-tight mb-2">Build Your Portfolio</h1>
            <p class="text-slate-400">Fill in your professional details to generate your dynamic page.</p>
        </header>

        <form id="detailsForm" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2 ml-1">Full Name</label>
                    <input type="text" id="inName" required placeholder="Your Full Name Here" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                </div>
                
                <div class="relative dropdown-container">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2 ml-1">DOB</label>
                    <button type="button" id="calToggle" class="w-full p-4 rounded-2xl text-left text-white input-glass flex justify-between items-center cursor-pointer">
                        <span id="dateDisplay" class="font-medium text-sm text-slate-400">Select Date</span>
                        <i data-lucide="calendar" class="w-5 h-5 text-iosBlue"></i>
                    </button>
                    <input type="hidden" id="inDob" required>

                    <div id="iosCalendar" class="hidden absolute left-0 right-0 md:right-auto md:w-80 z-[110] mt-4 glass rounded-[2.5rem] p-5 dropdown-animate">
                        <div class="flex justify-between items-center mb-4 px-1">
                            <div class="flex items-center gap-1 cursor-pointer hover:bg-white/10 p-1 px-2 rounded-lg transition" id="monthYearSelect">
                                <span id="currentMonth" class="font-bold text-sm">March</span>
                                <span id="currentYear" class="font-bold text-sm text-slate-400">2026</span>
                                <i data-lucide="chevron-down" class="w-3 h-3 text-slate-500 ml-1"></i>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" id="prevMonth" class="nav-btn p-1.5 rounded-lg hover:bg-white/10"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                                <button type="button" id="nextMonth" class="nav-btn p-1.5 rounded-lg hover:bg-white/10"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                            </div>
                        </div>
                        <div class="calendar-grid text-[9px] font-bold text-slate-500 mb-2 text-center uppercase tracking-widest">
                            <div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div class="text-iosBlue/70">Sa</div><div class="text-iosBlue/70">Su</div>
                        </div>
                        <div id="calendarDays" class="calendar-grid"></div>
                        <div class="mt-4 pt-4 border-t border-white/5 flex justify-center">
                            <button type="button" id="todayBtn" class="text-[11px] font-bold text-iosBlue uppercase tracking-widest hover:opacity-70">Back to Today</button>
                        </div>
                        <div id="pickerOverlay" class="hidden absolute inset-0 glass rounded-[2.5rem] z-[60] p-6 flex flex-col">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xs font-bold uppercase text-slate-400">Jump to Year</h3>
                                <button type="button" id="closePicker" class="text-iosBlue text-xs font-bold">Done</button>
                            </div>
                            <div class="grid grid-cols-3 gap-2 mb-6" id="monthPickerGrid"></div>
                            <div class="flex-1 overflow-y-auto picker-scroll pr-2 grid grid-cols-3 gap-2" id="yearPickerGrid"></div>
                        </div>
                    </div>
                </div>
                <div class="space-y-1 relative dropdown-container">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 ml-2">Gender</label>
                    <input type="hidden" id="gender" required>
                    <button type="button" id="gender-btn" class="dropdown-btn w-full p-4 rounded-2xl text-left text-slate-400 input-glass flex justify-between items-center relative z-10 cursor-pointer">
                        <span id="gender-text">Select</span>
                        <i data-lucide="chevron-down" class="text-slate-400 transition-transform duration-300"></i>
                    </button>
                    <ul id="gender-menu" class="dropdown-menu hidden absolute z-[100] w-full mt-2 rounded-2xl bg-[#0f172a]/95 backdrop-blur-3xl border border-white/10 shadow-2xl overflow-hidden transform opacity-0 scale-95 transition-all duration-300 pointer-events-none">
                        <li class="p-4 text-white hover:bg-white/10 cursor-pointer transition-colors border-b border-white/5" data-value="Male">Male</li>
                        <li class="p-4 text-white hover:bg-white/10 cursor-pointer transition-colors border-b border-white/5" data-value="Female">Female</li>
                        <li class="p-4 text-white hover:bg-white/10 cursor-pointer transition-colors" data-value="Other">Other</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2 ml-1">Nationality</label>
                    <input type="text" id="inNat" placeholder="Nationality" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                </div>

                <div class="space-y-1 relative dropdown-container">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 ml-2">Marital Status</label>
                    <input type="hidden" id="maritalStatus" required>
                    <button type="button" id="marital-btn" class="dropdown-btn w-full p-4 rounded-2xl text-left text-slate-400 input-glass flex justify-between items-center relative z-10 cursor-pointer">
                        <span id="marital-text">Select</span>
                        <i data-lucide="chevron-down" class="text-slate-400 transition-transform duration-300"></i>
                    </button>
                    <ul id="marital-menu" class="dropdown-menu hidden absolute z-[100] w-full mt-2 rounded-2xl bg-[#0f172a]/95 backdrop-blur-3xl border border-white/10 shadow-2xl overflow-hidden transform opacity-0 scale-95 transition-all duration-300 pointer-events-none">
                        <li class="p-4 text-white hover:bg-white/10 cursor-pointer transition-colors border-b border-white/5" data-value="Single">Single</li>
                        <li class="p-4 text-white hover:bg-white/10 cursor-pointer transition-colors" data-value="Married">Married</li>
                    </ul>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-white/5">
                <input type="email" id="inEmail" required placeholder="Email Address" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="tel" id="inPhone" required placeholder="Phone Number" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="text" id="inAddress" placeholder="Full Address (City, Country)" class="md:col-span-2 w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="text" id="inEdu" placeholder="Education (e.g. B.Tech Computer Science)" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="text" id="inJob" placeholder="Occupation / Role" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <textarea id="inSkills" placeholder="Skills (Comma separated: HTML, CSS, JavaScript)" rows="2" class="w-full p-4 rounded-2xl text-white input-glass outline-none"></textarea>
                <textarea id="inAbout" placeholder="Write a short bio about yourself..." rows="2" class="w-full p-4 rounded-2xl text-white input-glass outline-none"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-white/5">
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Profile Photo</label>
                    <input type="file" id="inPhoto" accept="image/*" class="w-full text-sm text-slate-400 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:bg-white/10 file:text-white cursor-pointer hover:file:bg-white/20 transition">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Resume (PDF)</label>
                    <input type="file" id="inResume" accept=".pdf" class="w-full text-sm text-slate-400 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:bg-white/10 file:text-white cursor-pointer hover:file:bg-white/20 transition">
                </div>
            </div>

            <button type="submit" class="w-full py-5 rounded-2xl bg-iosBlue text-white font-bold text-lg hover:shadow-[0_0_40px_-10px_rgba(0,122,255,0.8)] transition-all active:scale-[0.98]">
                Launch Portfolio
            </button>
        </form>
    </div>

    <main id="portfolioView" class="w-full max-w-6xl mx-auto pb-20">
        <section class="glass rounded-[3rem] p-8 md:p-16 mb-8 relative overflow-hidden flex flex-col items-center text-center">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-iosBlue/20 blur-[100px] -z-10 rounded-full"></div>
            <img id="outPhoto" src="https://api.dicebear.com/7.x/avataaars/svg?seed=User" class="w-40 h-40 md:w-56 md:h-56 rounded-full object-cover border-4 border-white/20 shadow-2xl mb-8" alt="Profile">
            <h1 id="outName" class="text-5xl md:text-7xl font-black tracking-tight mb-4"></h1>
            <p id="outJob" class="text-xl md:text-3xl text-iosBlue font-medium mb-8"></p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#" id="outEmailBtn" class="glass px-6 py-3 rounded-full flex items-center gap-2 hover:bg-white/10 transition">
                    <i data-lucide="mail" class="w-5 h-5 text-iosBlue"></i> <span id="outEmail"></span>
                </a>
                <a href="#" id="outPhoneBtn" class="glass px-6 py-3 rounded-full flex items-center gap-2 hover:bg-white/10 transition">
                    <i data-lucide="phone" class="w-5 h-5 text-iosBlue"></i> <span id="outPhone"></span>
                </a>
            </div>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-1 space-y-8">
                <div class="glass p-8 rounded-[2rem]">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-6">Personal Details</h3>
                    <ul class="space-y-4 text-slate-300 text-sm">
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>DOB:</span> <strong id="outDob" class="text-white"></strong></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Gender:</span> <strong id="outGender" class="text-white"></strong></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Marital Status:</span> <strong id="outMarital" class="text-white"></strong></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Nationality:</span> <strong id="outNat" class="text-white"></strong></li>
                        <li class="flex justify-between pb-2"><span>Location:</span> <strong id="outAddress" class="text-white text-right max-w-[60%]"></strong></li>
                    </ul>
                </div>
                <div class="glass p-8 rounded-[2rem] text-center">
                    <a id="downloadResumeBtn" href="#" class="w-full inline-flex justify-center items-center gap-2 bg-white text-slate-900 px-6 py-4 rounded-2xl font-bold hover:bg-slate-200 transition shadow-xl">
                        <i data-lucide="download"></i> Download Resume
                    </a>
                </div>
            </div>
            <div class="md:col-span-2 space-y-8">
                <div class="glass p-8 rounded-[2rem]">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-4">About Me</h3>
                    <p id="outAbout" class="text-lg text-slate-200 leading-relaxed"></p>
                </div>
                <div class="glass p-8 rounded-[2rem]">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-4">Education</h3>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-iosBlue/20 flex items-center justify-center shrink-0">
                            <i data-lucide="graduation-cap" class="text-iosBlue"></i>
                        </div>
                        <div>
                            <h4 id="outEdu" class="text-xl font-bold text-white"></h4>
                            <p class="text-slate-400">Completed Degree</p>
                        </div>
                    </div>
                </div>
                <div class="glass p-8 rounded-[2rem]">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-6">Technical Skills</h3>
                    <div id="outSkills" class="flex flex-wrap gap-3"></div>
                </div>
            </div>
        </div>
        <div class="mt-10 text-center"><button onclick="location.reload()" class="text-slate-500 hover:text-white underline text-sm transition">Start Over & Edit Details</button></div>
    </main>

    <script>
        lucide.createIcons();

        /* ------------------------------------------------------------------ */
        /* --- INTEGRATED CALENDAR LOGIC (From Program 2) ------------------- */
        /* ------------------------------------------------------------------ */
        let viewDate = new Date(); 
        let selectedDate = null;
        const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        const calToggle = document.getElementById('calToggle');
        const calMenu = document.getElementById('iosCalendar');
        const calDays = document.getElementById('calendarDays');
        const dateDisplay = document.getElementById('dateDisplay');
        const pickerOverlay = document.getElementById('pickerOverlay');
        const inDobHidden = document.getElementById('inDob');

        calToggle.onclick = (e) => { e.stopPropagation(); calMenu.classList.toggle('hidden'); renderCalendar(); };

        document.getElementById('monthYearSelect').onclick = () => {
            pickerOverlay.classList.remove('hidden');
            renderPickers();
        };
        document.getElementById('closePicker').onclick = () => pickerOverlay.classList.add('hidden');

        function renderCalendar() {
            calDays.innerHTML = '';
            const year = viewDate.getFullYear();
            const month = viewDate.getMonth();
            document.getElementById('currentMonth').innerText = months[month];
            document.getElementById('currentYear').innerText = year;

            let firstDay = new Date(year, month, 1).getDay();
            let dayOffset = firstDay === 0 ? 6 : firstDay - 1; 
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < dayOffset; i++) {
                const empty = document.createElement('div');
                empty.className = 'day-cell empty';
                calDays.appendChild(empty);
            }

            for (let i = 1; i <= daysInMonth; i++) {
                const day = document.createElement('div');
                day.className = 'day-cell';
                day.innerText = i;
                const isToday = new Date().toDateString() === new Date(year, month, i).toDateString();
                if (isToday) day.classList.add('today');
                if (selectedDate && selectedDate.toDateString() === new Date(year, month, i).toDateString()) {
                    day.classList.add('selected');
                }

                day.onclick = (e) => {
                    e.stopPropagation();
                    selectedDate = new Date(year, month, i);
                    const formattedDate = `${i < 10 ? '0' + i : i}-${month + 1 < 10 ? '0' + (month + 1) : month + 1}-${year}`;
                    dateDisplay.innerText = formattedDate;
                    dateDisplay.classList.remove('text-slate-400');
                    inDobHidden.value = formattedDate; // Bind to Program 1 logic
                    renderCalendar();
                    setTimeout(() => calMenu.classList.add('hidden'), 150);
                };
                calDays.appendChild(day);
            }
        }

        function renderPickers() {
            const mGrid = document.getElementById('monthPickerGrid');
            const yGrid = document.getElementById('yearPickerGrid');
            mGrid.innerHTML = ''; yGrid.innerHTML = '';
            months.forEach((m, i) => {
                const btn = document.createElement('button');
                btn.type = "button";
                btn.className = `p-2 text-[10px] rounded-lg transition ${viewDate.getMonth() === i ? 'bg-iosBlue text-white' : 'hover:bg-white/10 text-slate-300'}`;
                btn.innerText = m.substring(0, 3);
                btn.onclick = () => { viewDate.setMonth(i); renderCalendar(); renderPickers(); };
                mGrid.appendChild(btn);
            });
            for (let i = 1950; i <= 2050; i++) {
                const btn = document.createElement('button');
                btn.type = "button";
                btn.id = `year-${i}`;
                btn.className = `p-2 text-[10px] rounded-lg transition ${viewDate.getFullYear() === i ? 'bg-iosBlue text-white' : 'hover:bg-white/10 text-slate-300'}`;
                btn.innerText = i;
                btn.onclick = () => { viewDate.setFullYear(i); renderCalendar(); renderPickers(); };
                yGrid.appendChild(btn);
            }
            setTimeout(() => {
                const activeYear = document.getElementById(`year-${viewDate.getFullYear()}`);
                if(activeYear) activeYear.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }, 100);
        }

        document.getElementById('prevMonth').onclick = (e) => { e.stopPropagation(); viewDate.setMonth(viewDate.getMonth() - 1); renderCalendar(); };
        document.getElementById('nextMonth').onclick = (e) => { e.stopPropagation(); viewDate.setMonth(viewDate.getMonth() + 1); renderCalendar(); };
        document.getElementById('todayBtn').onclick = (e) => { e.stopPropagation(); viewDate = new Date(); renderCalendar(); };
        /* ------------------------------------------------------------------ */

        // --- DROPDOWN LOGIC (Program 1) ---
        function setupDropdown(btnId, menuId, textId, inputId) {
            const btn = document.getElementById(btnId);
            const menu = document.getElementById(menuId);
            const textDisplay = document.getElementById(textId);
            const hiddenInput = document.getElementById(inputId);
            const options = menu.querySelectorAll('li');

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const isClosed = menu.classList.contains('hidden');
                closeAllDropdowns();
                if (isClosed) {
                    menu.classList.remove('hidden', 'pointer-events-none');
                    btn.classList.add('active-glass');
                    requestAnimationFrame(() => {
                        menu.classList.remove('opacity-0', 'scale-95');
                        menu.classList.add('opacity-100', 'scale-100');
                        btn.querySelector('svg').classList.add('rotate-180', 'text-iosBlue');
                    });
                }
            });

            options.forEach(option => {
                option.addEventListener('click', (e) => {
                    const val = option.getAttribute('data-value');
                    textDisplay.textContent = val;
                    textDisplay.classList.replace('text-slate-400', 'text-white');
                    hiddenInput.value = val;
                    closeAllDropdowns();
                });
            });
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('opacity-100', 'scale-100');
                menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                setTimeout(() => { if (menu.classList.contains('opacity-0')) menu.classList.add('hidden'); }, 300);
            });
            document.querySelectorAll('.dropdown-btn').forEach(btn => {
                btn.classList.remove('active-glass');
                const icon = btn.querySelector('svg');
                if(icon) icon.classList.remove('rotate-180', 'text-iosBlue');
            });
            calMenu.classList.add('hidden'); // Also close calendar
        }

        setupDropdown('gender-btn', 'gender-menu', 'gender-text', 'gender');
        setupDropdown('marital-btn', 'marital-menu', 'marital-text', 'maritalStatus');

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown-container')) closeAllDropdowns();
        });

        // --- PORTFOLIO GENERATION LOGIC ---
        document.getElementById('detailsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!document.getElementById('gender').value || !document.getElementById('maritalStatus').value || !inDobHidden.value) {
                alert('Please fill all required fields including DOB, Gender and Marital Status.'); 
                return;
            }

            document.getElementById('outName').textContent = document.getElementById('inName').value;
            document.getElementById('outDob').textContent = inDobHidden.value;
            document.getElementById('outGender').textContent = document.getElementById('gender').value;
            document.getElementById('outNat').textContent = document.getElementById('inNat').value || 'N/A';
            document.getElementById('outMarital').textContent = document.getElementById('maritalStatus').value;
            document.getElementById('outEmail').textContent = document.getElementById('inEmail').value;
            document.getElementById('outEmailBtn').href = 'mailto:' + document.getElementById('inEmail').value;
            document.getElementById('outPhone').textContent = document.getElementById('inPhone').value;
            document.getElementById('outPhoneBtn').href = 'tel:' + document.getElementById('inPhone').value;
            document.getElementById('outAddress').textContent = document.getElementById('inAddress').value || 'N/A';
            document.getElementById('outEdu').textContent = document.getElementById('inEdu').value || 'N/A';
            document.getElementById('outJob').textContent = document.getElementById('inJob').value || 'Professional';
            document.getElementById('outAbout').textContent = document.getElementById('inAbout').value || 'No bio provided.';

            const skills = document.getElementById('inSkills').value.split(',');
            const skillContainer = document.getElementById('outSkills');
            skillContainer.innerHTML = '';
            skills.forEach(skill => {
                if (skill.trim()) {
                    const span = document.createElement('span');
                    span.className = "bg-white/10 border border-white/20 px-4 py-2 rounded-xl text-sm font-medium tracking-wide";
                    span.textContent = skill.trim();
                    skillContainer.appendChild(span);
                }
            });

            const photoFile = document.getElementById('inPhoto').files[0];
            if (photoFile) {
                const reader = new FileReader();
                reader.onload = (ev) => document.getElementById('outPhoto').src = ev.target.result;
                reader.readAsDataURL(photoFile);
            }

            const resumeFile = document.getElementById('inResume').files[0];
            const dlBtn = document.getElementById('downloadResumeBtn');
            if (resumeFile) {
                dlBtn.href = URL.createObjectURL(resumeFile);
                dlBtn.download = `${document.getElementById('inName').value.replace(/\s+/g, '_')}_Resume.pdf`;
            }

            const formView = document.getElementById('formView');
            const portView = document.getElementById('portfolioView');
            formView.classList.add('form-fade-out');
            setTimeout(() => {
                formView.style.display = 'none';
                portView.style.display = 'block'; 
                portView.classList.add('portfolio-pre-anim');
                requestAnimationFrame(() => {
                    portView.classList.remove('portfolio-pre-anim');
                    portView.classList.add('portfolio-focus-in');
                    window.scrollTo(0, 0);
                    lucide.createIcons();
                });
            }, 600); 
        });
    </script>
</body>
</html>
