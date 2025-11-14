/* Shared data manager & rendering for all pages */
/* Uses localStorage key "att_students_v1" */

(function(global){
  const KEY = "att_students_v1";

  const defaultStudents = [
    // sessions: array[6] boolean -> true means present
    // parts: array[6] boolean -> true means participated
    { id: "1001", last: "Ahmed", first: "Sara",
      sessions:[true,false,false,false,false,false],
      parts:[true,false,false,false,false,false]
    },
    { id: "1002", last: "Yacine", first: "Ali",
      sessions:[true,true,true,true,true,true],
      parts:[true,true,true,true,false,false]
    },
    { id: "1003", last: "Houcine", first: "Rania",
      sessions:[true,true,true,true,false,false],
      parts:[true,true,false,false,false,false]
    }
  ];

  function read(){ try{
    const raw = localStorage.getItem(KEY);
    if(!raw) return JSON.parse(JSON.stringify(defaultStudents));
    return JSON.parse(raw);
  }catch(e){ console.error(e); return JSON.parse(JSON.stringify(defaultStudents)); } }

  function write(data){ localStorage.setItem(KEY, JSON.stringify(data)); }

  function addStudent(obj){
    const list = read();
    list.push(obj);
    write(list);
  }

  function updateStudent(index, obj){
    const list = read();
    list[index] = obj;
    write(list);
  }

  function removeAll(){ localStorage.removeItem(KEY); }

  // compute absences & participation counts
  function countAbsences(student){
    return student.sessions.reduce((acc,v)=> acc + (v?0:1),0);
  }
  function countParticipation(student){
    return student.parts.reduce((acc,v)=> acc + (v?1:0),0);
  }

  // Render table into container (table element). callback for events.
  function renderTable(tableElement, opts={}){
    const students = read();
    const tbody = tableElement.tBodies[0] || tableElement.appendChild(document.createElement("tbody"));
    tbody.innerHTML = "";
    students.forEach((s, idx) => {
      const tr = tbody.insertRow();
      // names
      const tdLast = tr.insertCell(); tdLast.className="name"; tdLast.textContent = s.last;
      const tdFirst = tr.insertCell(); tdFirst.className="name"; tdFirst.textContent = s.first;
      // sessions and participation columns
      for(let i=0;i<6;i++){
        const tdS = tr.insertCell();
        tdS.innerHTML = `<label class="checkbox"><input type="checkbox" data-row="${idx}" data-type="session" data-i="${i}" ${s.sessions[i] ? "checked":""}></label>`;
        const tdP = tr.insertCell();
        tdP.innerHTML = `<label class="checkbox"><input type="checkbox" data-row="${idx}" data-type="part" data-i="${i}" ${s.parts[i] ? "checked":""}></label>`;
      }
      // absences / participation counts
      const abs = countAbsences(s);
      const par = countParticipation(s);
      const tdAbs = tr.insertCell(); tdAbs.textContent = abs;
      const tdPar = tr.insertCell(); tdPar.textContent = par;
      // message
      const tdMsg = tr.insertCell(); tdMsg.className = "msg";
      if(abs < 3 && par >=3) tdMsg.textContent = "Good attendance – Excellent participation";
      else if(abs >=3 && abs <=4) tdMsg.textContent = "Warning – attendance low – You need to participate more";
      else if(abs >=5) tdMsg.textContent = "Excluded – too many absences – You need to participate more";
      else tdMsg.textContent = "Keep going";

      // highlight row
      tr.classList.remove("good","warn","bad");
      if(abs < 3) tr.classList.add("good");
      else if(abs <=4) tr.classList.add("warn");
      else tr.classList.add("bad");

      // attach change listeners to checkboxes
      tr.querySelectorAll('input[type="checkbox"]').forEach(ch => {
        ch.addEventListener('change', function(e){
          const rowIdx = parseInt(this.dataset.row,10);
          const type = this.dataset.type;
          const i = parseInt(this.dataset.i,10);
          const data = read();
          if(type === "session") data[rowIdx].sessions[i] = this.checked;
          else data[rowIdx].parts[i] = this.checked;
          write(data);
          // re-render (simple approach)
          if(typeof opts.onChange === "function") opts.onChange(rowIdx);
          renderTable(tableElement, opts);
        });
      });

      // store dataset for later use
      tr.dataset.index = idx;
    });
  }

  // expose
  global.Attendance = {
    read, write, addStudent, updateStudent, removeAll,
    countAbsences, countParticipation,
    renderTable
  };

})(window);
