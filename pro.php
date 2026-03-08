<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Portfolio System | G Dinesh</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { background: #0f172a; font-family: 'Inter', sans-serif; color: white; overflow-x: hidden; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .input-glass { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); transition: all 0.3s ease; }
        .input-glass:focus { border-color: #007AFF; background: rgba(255, 255, 255, 0.08); box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.15); }
        
        /* THE FIX: Forces the dropdown options to be dark instead of white */
        select option { background-color: #1e293b; color: white; }
        
        .section-hidden { display: none; opacity: 0; transform: translateY(20px); transition: all 0.6s ease; }
        .section-visible { display: block; opacity: 1 !important; transform: translateY(0) !important; }
    </style>
</head>
<body class="min-h-screen p-4 md:p-10 flex flex-col items-center">

    <div id="formSection" class="w-full max-w-4xl glass rounded-[2.5rem] p-8 md:p-12 shadow-2xl section-visible">
        <header class="mb-10 text-center">
            <h2 class="text-3xl font-extrabold text-white mb-2">Personal Details Form</h2>
            <p class="text-slate-400 text-sm">Fill in your information to generate your portfolio instantly.</p>
        </header>

        <form id="masterForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="text" id="inName" required placeholder="Full Name" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="date" id="inDob" required class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                
                <select id="inGender" required class="w-full p-4 rounded-2xl text-white input-glass outline-none cursor-pointer">
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                
                <input type="email" id="inEmail" required placeholder="Email Address" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="tel" id="inPhone" required placeholder="Phone Number" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
                <input type="text" id="inEdu" placeholder="Education (e.g. B.Tech CS)" class="w-full p-4 rounded-2xl text-white input-glass outline-none">
            </div>
            
            <div class="space-y-4">
                <textarea id="inAddress" placeholder="Full Address" rows="2" class="w-full p-4 rounded-2xl text-white input-glass outline-none"></textarea>
                <textarea id="inSkills" placeholder="Skills (Comma separated: Java, AI, Web Development...)" rows="2" class="w-full p-4 rounded-2xl text-white input-glass outline-none"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-white/5">
                <div class="space-y-1">
                    <label class="text-xs text-slate-500 ml-2 font-bold uppercase">Profile Photo</label>
                    <input type="file" id="inPhoto" accept="image/*" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-blue-600 file:text-white cursor-pointer">
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-slate-500 ml-2 font-bold uppercase">Resume (PDF)</label>
                    <input type="file" id="inResume" accept=".pdf" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-slate-700 file:text-white cursor-pointer">
                </div>
            </div>

            <button type="submit" class="w-full py-5 rounded-3xl bg-blue-600 text-white font-bold text-lg hover:shadow-lg hover:shadow-blue-500/30 transition-all active:scale-95">
                Generate Portfolio
            </button>
        </form>
    </div>

    <div id="portfolioDisplay" class="section-hidden w-full max-w-5xl space-y-10 pb-20">
        <div class="glass rounded-[3rem] p-10 flex flex-col md:flex-row items-center gap-10">
            <div class="relative">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full blur opacity-50"></div>
                <img id="outPhoto" src="https://api.dicebear.com/7.x/avataaars/svg?seed=Dinesh" class="relative w-48 h-48 rounded-full object-cover border-4 border-white/10" alt="Profile">
            </div>
            <div class="text-center md:text-left space-y-4">
                <h2 id="outName" class="text-5xl font-extrabold tracking-tight"></h2>
                <p id="outEdu" class="text-2xl text-blue-400 font-medium"></p>
                <div class="flex flex-wrap justify-center md:justify-start gap-3">
                    <span class="bg-white/5 px-4 py-2 rounded-full text-sm border border-white/10 flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4"></i> <span id="outEmail"></span>
                    </span>
                    <span class="bg-white/5 px-4 py-2 rounded-full text-sm border border-white/10 flex items-center gap-2">
                        <i data-lucide="phone" class="w-4 h-4"></i> <span id="outPhone"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="md:col-span-2 glass rounded-[2.5rem] p-8 space-y-6">
                <h3 class="text-2xl font-bold flex items-center gap-3"><i data-lucide="user" class="text-blue-500"></i> About Me</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-300">
                    <p><strong>DOB:</strong> <span id="outDob"></span></p>
                    <p><strong>Gender:</strong> <span id="outGender"></span></p>
                    <p class="md:col-span-2"><strong>Address:</strong> <span id="outAddress"></span></p>
                </div>
                <div id="resumeContainer" class="pt-4">
                    <a id="downloadBtn" href="#" download class="inline-flex items-center gap-2 bg-white text-slate-900 px-6 py-3 rounded-full font-bold hover:bg-slate-200 transition">
                        <i data-lucide="download"></i> Download Resume
                    </a>
                </div>
            </div>
            <div class="glass rounded-[2.5rem] p-8">
                <h3 class="text-2xl font-bold mb-6 flex items-center gap-3"><i data-lucide="zap" class="text-blue-500"></i> Expertise</h3>
                <div id="outSkills" class="flex flex-wrap gap-2"></div>
            </div>
        </div>
        
        <div class="text-center">
            <button onclick="location.reload()" class="px-8 py-3 glass rounded-full font-bold hover:bg-white/5 transition border border-white/10">
                Create New Portfolio
            </button>
        </div>
    </div>

    <script>
        lucide.createIcons();

        document.getElementById('masterForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Map Text
            document.getElementById('outName').innerText = document.getElementById('inName').value;
            document.getElementById('outEdu').innerText = document.getElementById('inEdu').value;
            document.getElementById('outEmail').innerText = document.getElementById('inEmail').value;
            document.getElementById('outPhone').innerText = document.getElementById('inPhone').value;
            document.getElementById('outDob').innerText = document.getElementById('inDob').value;
            document.getElementById('outGender').innerText = document.getElementById('inGender').value;
            document.getElementById('outAddress').innerText = document.getElementById('inAddress').value;

            // Map Skills
            const skillsArr = document.getElementById('inSkills').value.split(',');
            const skillsContainer = document.getElementById('outSkills');
            skillsContainer.innerHTML = '';
            skillsArr.forEach(skill => {
                if(skill.trim()) {
                    const span = document.createElement('span');
                    span.className = "bg-blue-600/20 text-blue-400 border border-blue-400/30 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider";
                    span.innerText = skill.trim();
                    skillsContainer.appendChild(span);
                }
            });

            // Handle Files
            const photoFile = document.getElementById('inPhoto').files[0];
            if (photoFile) {
                const reader = new FileReader();
                reader.onload = (ev) => document.getElementById('outPhoto').src = ev.target.result;
                reader.readAsDataURL(photoFile);
            }

            const resumeFile = document.getElementById('inResume').files[0];
            if (resumeFile) {
                const fileURL = URL.createObjectURL(resumeFile);
                const dlBtn = document.getElementById('downloadBtn');
                dlBtn.href = fileURL;
                dlBtn.download = `${document.getElementById('inName').value}_Resume.pdf`;
            }

            // Animation
            const form = document.getElementById('formSection');
            const port = document.getElementById('portfolioDisplay');

            form.style.display = 'none';
            port.classList.remove('section-hidden');
            port.classList.add('section-visible');

            lucide.createIcons();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    </script>
</body>
</html>