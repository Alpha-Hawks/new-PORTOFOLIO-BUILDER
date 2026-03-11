<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Portfolio & iOS Calendar Pro</title>
    
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
        
        .glass {
            background: rgba(15, 23, 42, 0.85);
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

        .form-fade-out {
            opacity: 0; transform: scale(0.95); filter: blur(10px);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: none;
        }

        #portfolioView { display: none; }
        .portfolio-pre-anim { opacity: 0; filter: blur(40px); transform: scale(0.85); }
        .portfolio-focus-in {
            opacity: 1; filter: blur(0px); transform: scale(1);
            transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Timeline Connector Style */
        .timeline-line::after {
            content: '';
            position: absolute;
            left: 23px;
            top: 48px;
            bottom: -24px;
            width: 2px;
            background: rgba(255,255,255,0.1);
        }
        .timeline-item:last-child .timeline-line::after { display: none; }
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

                    <div id="iosCalendar" class="hidden absolute left-0 right-0 md:left-auto md:w-80 z-[999] mt-4 glass rounded-[2.5rem] p-5">
                        <div class="flex justify-between items-center mb-4 px-1">
                            <div class="flex items-center gap-1 cursor-pointer hover:bg-white/10 p-1 px-2 rounded-lg transition" id="monthYearSelect">
                                <span id="currentMonth" class="font-bold text-sm"></span>
                                <span id="currentYear" class="font-bold text-sm text-slate-400"></span>
                                <i data-lucide="chevron-down" class="w-3 h-3 text-slate-500 ml-1"></i>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" id="prevMonth" class="p-1.5 rounded-lg hover:bg-white/10 transition"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                                <button type="button" id="nextMonth" class="p-1.5 rounded-lg hover:bg-white/10 transition"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
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
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <ul id="gender-menu" class="dropdown-menu hidden absolute z-[100] w-full mt-2 rounded-2xl bg-[#0f172a]/95 backdrop-blur-3xl border border-white/10 shadow-2xl overflow-hidden transform opacity-0 scale-95 transition-all duration-300 pointer-events-none">
                        <li class="p-4 text-white hover:bg-white/10 cursor-pointer border-b border-white/5" data-value="Male">Male</li>
                        <li class="p-4 text-white hover:bg-white/10 cursor-pointer border-b border-white/5" data-value="Female">Female</li>
                        <li class="p-4 text-white hover:bg-white/10 cursor-pointer" data-value="Other">Other</li>
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
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <ul id="marital-menu" class="dropdown-menu hidden absolute z-[100] w-full mt-2 rounded-2xl bg-[#0f172a]/95 backdrop-blur-3xl border border-white/10 shadow-2xl overflow-hidden transform opacity-0 scale-95 transition-all duration-300 pointer-events-none">
                        <li class="p-4 text-white hover:bg-white/10 cursor-pointer border-b border-white/5" data-value="Single">Single</li>
                        <li class="p-4 text-white hover:bg-white/10 cursor-pointer" data-value="Married">Married</li>
                    </ul>
                </div>
            </div>

            <div class="pt-4 border-t border-white/5">
                <h3 class="text-xs font-bold uppercase tracking-widest text-iosBlue mb-6 ml-1">Education History</h3>
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <input type="text" id="inCollege" placeholder="Degree College Name" class="md:col-span-2 p-4 rounded-2xl text-white input-glass outline-none">
                        <input type="number" id="inCollegeStart" placeholder="joining year" class="p-4 rounded-2xl text-white input-glass outline-none">
                        <input type="number" id="inCollegeEnd" placeholder="End Year" class="p-4 rounded-2xl text-white input-glass outline-none">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <input type="text" id="inJuniorCollege" placeholder="Junior College Name" class="md:col-span-2 p-4 rounded-2xl text-white input-glass outline-none">
                        <input type="number" id="inJuniorStart" placeholder="joining year" class="p-4 rounded-2xl text-white input-glass outline-none">
                        <input type="number" id="inJuniorEnd" placeholder="End Year" class="p-4 rounded-2xl text-white input-glass outline-none">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <input type="text" id="inSchool" placeholder="School Name" class="md:col-span-2 p-4 rounded-2xl text-white input-glass outline-none">
                        <input type="number" id="inSchoolStart" placeholder="joining year" class="p-4 rounded-2xl text-white input-glass outline-none">
                        <input type="number" id="inSchoolEnd" placeholder="End Year" class="p-4 rounded-2xl text-white input-glass outline-none">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-white/5">
                <input type="email" id="inEmail" required placeholder="Email Address" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="tel" id="inPhone" required placeholder="Phone Number" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="text" id="inAddress" placeholder="Full Address (City, Country)" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="text" id="inPostal" placeholder="Postal Code" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="url" id="inGithub" placeholder="GitHub Link" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="url" id="inLinkedin" placeholder="LinkedIn Link" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="text" id="inJob" placeholder="Field Related To" class="md:col-span-2 w-full p-4 rounded-2xl text-white input-glass outline-none">
               <textarea id="inAbout" placeholder="Short bio..." rows="2" class="w-full p-4 rounded-2xl text-white input-glass outline-none"></textarea>
                <textarea id="inSkills" placeholder="Skills (Comma separated)" rows="2" class="w-full p-4 rounded-2xl text-white input-glass outline-none"></textarea>
               <textarea id="inProjects" placeholder="Projects Done (Comma separated)" rows="2" class="md:col-span-2 w-full p-4 rounded-2xl text-white input-glass outline-none"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-white/5">
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Profile Photo</label>
                    <input type="file" id="inPhoto" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:bg-white/10 file:text-white cursor-pointer transition hover:file:bg-white/20">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Resume (PDF)</label>
                    <input type="file" id="inResume" accept=".pdf" class="w-full text-sm text-slate-400 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:bg-white/10 file:text-white cursor-pointer transition hover:file:bg-white/20">
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
            <h1 id="outName" class="text-5xl md:text-7xl font-black tracking-tight mb-4 text-white"></h1>
            <p id="outJob" class="text-xl md:text-3xl text-iosBlue font-medium mb-8 uppercase tracking-widest"></p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#" id="outEmailBtn" class="glass px-6 py-3 rounded-full flex items-center gap-2 hover:bg-white/10 transition"><i data-lucide="mail" class="w-5 h-5 text-iosBlue"></i> <span id="outEmail"></span></a>
                <a href="#" id="outGithub" target="_blank" class="glass px-6 py-3 rounded-full flex items-center gap-2 hover:bg-white/10 transition"><i data-lucide="github" class="w-5 h-5 text-iosBlue"></i> GitHub</a>
                <a href="#" id="outLinkedin" target="_blank" class="glass px-6 py-3 rounded-full flex items-center gap-2 hover:bg-white/10 transition"><i data-lucide="linkedin" class="w-5 h-5 text-iosBlue"></i> LinkedIn</a>
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
                        <li class="flex flex-col border-b border-white/5 pb-2"><span class="mb-1">Location:</span> <strong id="outAddress" class="text-white text-right"></strong></li>
                        <li class="flex justify-between pb-2"><span>Postal Code:</span> <strong id="outPostal" class="text-white"></strong></li>
                    </ul>
                </div>
                <div class="glass p-8 rounded-[2rem] text-center">
                    <a id="downloadResumeBtn" href="#" class="w-full inline-flex justify-center items-center gap-2 bg-white text-slate-900 px-6 py-4 rounded-2xl font-bold hover:bg-slate-200 transition shadow-xl"><i data-lucide="download"></i> Download Resume</a>
                </div>
            </div>
            
            <div class="md:col-span-2 space-y-8">
                <div class="glass p-8 rounded-[2rem]">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-4">About Me</h3>
                    <p id="outAbout" class="text-lg text-slate-200 leading-relaxed"></p>
                </div>
                
                <div class="glass p-8 rounded-[2rem]">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-6">Education</h3>
                    <div id="outEducationTimeline" class="space-y-8 relative">
                        </div>
                </div>

                <div class="glass p-8 rounded-[2rem]">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-4">Projects</h3>
                    <div id="outProjects" class="grid grid-cols-1 gap-4"></div>
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

        // CALENDAR LOGIC (Program 2)
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
        document.getElementById('monthYearSelect').onclick = (e) => { e.stopPropagation(); pickerOverlay.classList.remove('hidden'); renderPickers(); };
        document.getElementById('closePicker').onclick = (e) => { e.stopPropagation(); pickerOverlay.classList.add('hidden'); };

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
                if (new Date().toDateString() === new Date(year, month, i).toDateString()) day.classList.add('today');
                if (selectedDate && selectedDate.toDateString() === new Date(year, month, i).toDateString()) day.classList.add('selected');
                day.onclick = (e) => {
                    e.stopPropagation();
                    selectedDate = new Date(year, month, i);
                    const formattedDate = `${i < 10 ? '0' + i : i}-${month + 1 < 10 ? '0' + (month + 1) : month + 1}-${year}`;
                    dateDisplay.innerText = formattedDate;
                    dateDisplay.classList.remove('text-slate-400');
                    inDobHidden.value = formattedDate;
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
                btn.onclick = (e) => { e.stopPropagation(); viewDate.setMonth(i); renderCalendar(); renderPickers(); };
                mGrid.appendChild(btn);
            });
            for (let i = 1950; i <= 2050; i++) {
                const btn = document.createElement('button');
                btn.type = "button"; btn.id = `year-${i}`;
                btn.className = `p-2 text-[10px] rounded-lg transition ${viewDate.getFullYear() === i ? 'bg-iosBlue text-white' : 'hover:bg-white/10 text-slate-300'}`;
                btn.innerText = i;
                btn.onclick = (e) => { e.stopPropagation(); viewDate.setFullYear(i); renderCalendar(); renderPickers(); };
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

        // DROP-DOWN LOGIC
        function setupDropdown(btnId, menuId, textId, inputId) {
            const btn = document.getElementById(btnId);
            const menu = document.getElementById(menuId);
            const textDisplay = document.getElementById(textId);
            const hiddenInput = document.getElementById(inputId);
            const options = menu.querySelectorAll('li');
            btn.addEventListener('click', (e) => {
                e.preventDefault(); e.stopPropagation();
                const isClosed = menu.classList.contains('hidden');
                closeAllDropdowns();
                if (isClosed) {
                    menu.classList.remove('hidden', 'pointer-events-none');
                    btn.classList.add('active-glass');
                    requestAnimationFrame(() => { menu.classList.remove('opacity-0', 'scale-95'); menu.classList.add('opacity-100', 'scale-100'); });
                }
            });
            options.forEach(option => {
                option.addEventListener('click', (e) => {
                    const val = option.getAttribute('data-value');
                    textDisplay.textContent = val;
                    textDisplay.classList.replace('text-slate-400', 'text-white');
                    hiddenInput.value = val; closeAllDropdowns();
                });
            });
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('opacity-100', 'scale-100');
                menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                setTimeout(() => { if (menu.classList.contains('opacity-0')) menu.classList.add('hidden'); }, 300);
            });
            document.querySelectorAll('.dropdown-btn').forEach(btn => btn.classList.remove('active-glass'));
            calMenu.classList.add('hidden');
        }

        setupDropdown('gender-btn', 'gender-menu', 'gender-text', 'gender');
        setupDropdown('marital-btn', 'marital-menu', 'marital-text', 'maritalStatus');
        document.addEventListener('click', (e) => { if (!e.target.closest('.dropdown-container')) closeAllDropdowns(); });

        // FORM SUBMISSION
        document.getElementById('detailsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            document.getElementById('outName').textContent = document.getElementById('inName').value;
            document.getElementById('outDob').textContent = inDobHidden.value;
            document.getElementById('outGender').textContent = document.getElementById('gender').value;
            document.getElementById('outNat').textContent = document.getElementById('inNat').value || 'N/A';
            document.getElementById('outMarital').textContent = document.getElementById('maritalStatus').value;
            document.getElementById('outEmail').textContent = document.getElementById('inEmail').value;
            document.getElementById('outEmailBtn').href = 'mailto:' + document.getElementById('inEmail').value;
            document.getElementById('outAddress').textContent = document.getElementById('inAddress').value || 'N/A';
            document.getElementById('outPostal').textContent = document.getElementById('inPostal').value || 'N/A';
            document.getElementById('outJob').textContent = document.getElementById('inJob').value || 'N/A';
            document.getElementById('outAbout').textContent = document.getElementById('inAbout').value || 'No bio provided.';
            
            document.getElementById('outGithub').href = document.getElementById('inGithub').value || '#';
            document.getElementById('outLinkedin').href = document.getElementById('inLinkedin').value || '#';

            // Education Timeline Logic
            const timeline = document.getElementById('outEducationTimeline');
            timeline.innerHTML = '';
            const eduData = [
                { name: document.getElementById('inCollege').value, start: document.getElementById('inCollegeStart').value, end: document.getElementById('inCollegeEnd').value, label: 'Degree College' },
                { name: document.getElementById('inJuniorCollege').value, start: document.getElementById('inJuniorStart').value, end: document.getElementById('inJuniorEnd').value, label: 'Junior College' },
                { name: document.getElementById('inSchool').value,end: document.getElementById('inSchoolEnd').value, label: 'School' }
            ];

            eduData.forEach(item => {
                if(item.name) {
                    const div = document.createElement('div');
                    div.className = "timeline-item flex gap-6 relative";
                    div.innerHTML = `
                        <div class="timeline-line shrink-0 w-12 h-12 rounded-xl bg-iosBlue/20 flex items-center justify-center relative z-10">
                            <i data-lucide="graduation-cap" class="text-iosBlue w-6 h-6"></i>
                        </div>
                        <div class="pb-8">
                            <h4 class="text-xl font-bold text-white">${item.name}</h4>
                            <p class="text-iosBlue text-sm font-medium uppercase tracking-wide">${item.label}</p>
                            <p class="text-slate-400 text-sm mt-1">${item.start} — ${item.end}</p>
                        </div>
                    `;
                    timeline.appendChild(div);
                }
            });

            const projContainer = document.getElementById('outProjects');
            projContainer.innerHTML = '';
            document.getElementById('inProjects').value.split(',').forEach(p => {
                if(p.trim()){
                    const div = document.createElement('div');
                    div.className = "p-4 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-3 transition hover:bg-white/10";
                    div.innerHTML = `<i data-lucide="folder-code" class="text-iosBlue"></i> <span class="text-white font-medium">${p.trim()}</span>`;
                    projContainer.appendChild(div);
                }
            });

            const skillContainer = document.getElementById('outSkills');
            skillContainer.innerHTML = '';
            document.getElementById('inSkills').value.split(',').forEach(skill => {
                if (skill.trim()) {
                    const span = document.createElement('span');
                    span.className = "bg-white/10 border border-white/20 px-4 py-2 rounded-xl text-sm font-medium";
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
                dlBtn.download = `${document.getElementById('inName').value}_Resume.pdf`;
                dlBtn.style.display = 'inline-flex';
            } else { dlBtn.style.display = 'none'; }

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

