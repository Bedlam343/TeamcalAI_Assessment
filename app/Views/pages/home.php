<!doctype html>
<html>
  <head>
    <meta charset="UTF-8">  
    <title>Calendly Calendar Extractor</title>

    <!-- Load Tailwind CSS via Play CDN (development only) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Load React and Babel -->
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  </head>

  <body class="bg-light">
    <div id="react-root"></div>

    <script>
      let data_arrived = false;
    </script>

     <?php if (isset($avail_data) && is_array($avail_data)): ?>
      <script>
        const avail_data = <?= json_encode($avail_data); ?>;
        data_arrived = true;
        console.log(avail_data);
      </script>
    <?php endif; ?>

    <script type="text/babel">
      const MONTH_INDEX = {
        January: 0,
        February: 1,
        March: 2,
        April: 3,
        May: 4,
        June: 5,
        July: 6,
        August: 7,
        September: 8,
        October: 9,
        November: 10,
        December: 11,
      };

      function splitArray(inputArray, chunkSize = 7) {  
        const result = inputArray.reduce((resultArray, item, index) => { 
          const chunkIndex = Math.floor(index/chunkSize)

          if(!resultArray[chunkIndex]) {
            resultArray[chunkIndex] = []
          }

          resultArray[chunkIndex].push(item)

          return resultArray
        }, []);

        return result;
      }

      function getDayOfWeek(year, month, day) {
        const date = new Date(year, month, day);
        const dayNumber = date.getDay(); // Returns 0 (Sunday) to 6 (Saturday)
        
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return {
          day: days[dayNumber],
          dayNumber: (dayNumber + 6) % 7, // Now 0 (Monday) to 6 (Sunday)
        }
      }

      function TimeSlots({ year, month, monthDay, timeSlots }) {
        const { day: dayOfWeek } = getDayOfWeek(year, MONTH_INDEX[month], monthDay);

        return (
          <div className="flex justify-center">
            <div className="w-[500px]">
              <p className="text-lg font-semibold mb-3">{dayOfWeek}, {month} {monthDay}</p>
              <div className="flex flex-wrap gap-4">
                {timeSlots.map((slot) => (
                  <div key={slot} className="flex items-center justify-center w-[100px] h-[40px] border-[1px] border-gray-300 rounded-md px-3 py-1 hover:cursor-pointer hover:border-gray-500">
                    <p>{slot}</p>   
                  </div>
                ))}
              </div>
            </div>
          </div>
        );
      }

      function Availability() {
        const [activeDay, setActiveDay] = React.useState(null);

        function handleDayClick(year, month, monthDay, timeSlots) {
          setActiveDay({ year, month, monthDay, timeSlots });
        }

        return (
          <div className="flex flex-col items-center">
            <div className="flex flex-col gap-8">
              <p className="text-2xl text-center">Availability (Next 4 Weeks)</p>
              <div className="flex flex-col gap-6">
                {Object.keys(avail_data).map((year) => {
                  const months = avail_data[year];

                  return Object.keys(months).map((month) => {
                    const days = months[month];
                    const { dayNumber: dayOfWeek } = getDayOfWeek(year, MONTH_INDEX[month], days[0].monthDay);

                    const emptyDays = Array.from({length: dayOfWeek}, () => ({ monthDay: undefined }));

                    const allDays = [...emptyDays, ...days];
                    const weeks = splitArray(allDays, 7);

                    function isActive(year, month, monthDay) {
                      if(year === activeDay?.year && month === activeDay?.month && monthDay === activeDay?.monthDay) return true;
                      return false;
                    }

                    return (
                      <div key={`${year}-${month}`} className="flex flex-col items-center ">
                        <div>
                          <p className="text-xl mb-2">{month} {year}</p>

                          <div className="border-[1px] border-black rounded-lg px-2 py-2">
                            <table className="">
                              <thead className="">
                                <tr>
                                  {["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"].map((day) => (
                                    <td key={day} className="px-2 py-1 text-center w-[40px] font-semibold">{day}</td>
                                  ))}
                                </tr>
                              </thead>

                              <tbody>
                                {weeks.map((week) => (
                                  <tr key={`${year}-${month}-${JSON.stringify(week)}`}>
                                    {week.map((day) => (
                                      <td className={`px-1 py-1 w-[45px] rounded-full ${isActive(year, month, day.monthDay) ? "bg-blue-300" : ""} ${!day.timeSlots ? "" : "hover:bg-blue-300"}`} key={`${year}-${month}-${day.monthDay ?? Math.random()}`}>
                                          <button onClick={() => handleDayClick(year, month, day.monthDay, day.timeSlots)} className={`w-full text-center ${!day.timeSlots ? "text-gray-400" : ""}`} disabled={!day.timeSlots}>{day.monthDay}</button>
                                      </td>
                                    ))}
                                  </tr>
                                ))}
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    );
                  })
                })}
              </div>
            </div>

            {activeDay && <div className="mt-[50px]">
              <TimeSlots {...activeDay} />
            </div>}
          </div>
        );
      }
    </script>
    
    <script type="text/babel">
      function Form() {
        return (
          <div>
            <p 
              className="text-4xl text-center underline decoration-2 underline-offset-4 decoration-blue-600 decoration-wavy"
            >
              Calendly Schedule Extractor
            </p>

            <form action="/" method="post" className="mt-8">
              <div className="flex flex-col items-center">
                <div>
                  <label htmlFor="calendly_url" className="block font-bold text-stone-700">Calendly Link</label>
                  <input 
                    className="w-[400px] border-2 border-grey-500 h-[40px] px-1 rounded-md outline-none focus:border-grey-800" 
                    type="url"
                    id="calendly_url" 
                    name="calendly_url" 
                    required 
                    placeholder="https://calendly.com/username/event" 
                  />
                </div>
              </div>

              <div className="mt-6 flex justify-center">
                <button type="submit" className="text-stone-800 px-4 py-2 rounded-md bg-blue-200 hover:cursor-pointer hover:bg-blue-100">
                  Extract Availability
                </button>
              </div>
            </form>
          </div>
        )
      }

      function Home() {            
          return (
            <div className="my-8">
              <Form />
              <div className="h-[40px]" />
              {data_arrived && <Availability />}
            </div>
          );
      }

      ReactDOM.createRoot(document.getElementById('react-root')).render(<Home />);
    </script>

  </body>
</html>