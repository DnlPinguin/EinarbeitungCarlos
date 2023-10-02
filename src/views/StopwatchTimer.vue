<template >
  <div id="timer">
    <div class="bg-light">
      <p class="presentation">{{ name }} Einarbeitung</p>
      <div id="currentDayAndDate">
        <center>{{ getTheCurrentDay() }}. {{ getCurrentDate() }}</center>
      </div>
      <div id="RegisteredTime">Eingestempelt um {{ getTheCurrentTime() }}</div>
      <div id="workingTime">
        <h4>
          <b> {{ secondsToHMS(worktime) }}</b>
        </h4>
      </div>
      <div id="pauseTime">
        <div>
          <center>
            <small>Pause</small>
            <br />
            {{ secondsToHMS(pauseTime) }}
          </center>
        </div>
      </div>
    </div>
    <div>
      <div v-if="isStart">
        <div>
          <div v-if="!isPause">
            <div class="pause btn btn-orange w-100" @click="StartPause">
              Pause starten
            </div>
          </div>
          <div v-else>
            <div class="pause btn btn-green w-100" @click="StopPause">
              Pause beeden
            </div>
          </div>
        </div>
        <div class="Reset btn btn-red w-100" @click="reset">Ausstempeln</div>
      </div>
      <div v-else>
        <div class="start btn btn-green w-100" @click="start">Einstempeln</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "StopwatchTimer",

  components: {},

  props: {},

  data() {
    return {
      isPause: false,
      isReadyToStart: true,
      isStart: false,
      timeInSecond: 0,
      name: "carlos",
      startTime: null,
      pauseTime: 0,
      worktime: 0,
      registeredTime: 0,
      workingTimeInterval: null,
      pauseTimeInterval: null,
    };
  },
  methods: {
    start() {
      this.isStart = true;
      this.isPause = false;
      this.isReadyToStart = true;
      if (this.isReadyToStart) {
        this.workingTimeInterval = setInterval(() => {
          this.worktime++;
        }, 1000);
      }
    },
    StartPause() {
      clearInterval(this.workingTimeInterval);
      this.isPause = true;
      if (this.isPause) {
        this.pauseTimeInterval = setInterval(() => {
          this.pauseTime++;
        }, 1000);
      }
    },
    StopPause() {
      clearInterval(this.pauseTimeInterval);
      this.isPause = false;
      this.isReadyToStart = true;
      this.start();
    },
    reset() {
      this.isStart = false;
      clearInterval(this.pauseTimeInterval);
      clearInterval(this.workingTimeInterval);
    },
    secondsToHMS(seconds) {
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      const remainingSeconds = seconds % 60;

      const formattedHours = String(hours).padStart(2, "0");
      const formattedMinutes = String(minutes).padStart(2, "0");
      const formattedSeconds = String(remainingSeconds).padStart(2, "0");
      const timeString = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;

      return timeString;
    },
    getTheCurrentDay() {
      const currentDate = new Date();
      const dayOfWeek = currentDate.getDay();

      const dayNames = ["sun", "Mo", "di", "do", "mit", "fr", "sat"];

      const currentDay = dayNames[dayOfWeek];
      return currentDay;
    },
    getTheCurrentTime() {
      const currentDate = new Date();
      const hours = currentDate.getHours();
      const minutes = currentDate.getMinutes();
      const formattedHours = String(hours).padStart(2, "0");
      const formattedMinutes = String(minutes).padStart(2, "0");
      const currentTime = `${formattedHours}:${formattedMinutes}`;
      return currentTime;
    },
    getCurrentDate() {
      const currentDate = new Date();
      const day = currentDate.getDate();
      const month = currentDate.getMonth() + 1; // Months are zero-based, so add 1
      const year = currentDate.getFullYear();
      const formattedDay = String(day).padStart(2, "0");
      const formattedMonth = String(month).padStart(2, "0");
      const dateString = `${formattedDay}.${formattedMonth}.${year}`;

      return dateString;
    },
  },

  computed: {},

  watch: {},

  beforeDestroy() {},

  beforeMount() {},

  mounted() {},
};
</script>

<style scoped>
.bg-light {
  background-color: #e9e9e9;
  color: #000;
  padding: 20px;
  border-radius: 10px;
}
#timer {
  border: 1px solid #999 !important;
  padding: 40px;
  border-radius: 10px;
}
.w-100 {
  width: 100% !important;
}
.btn-green {
  background: #7fc167 !important;
  border-color: #69a054 !important;
}
.presentation {
  font-weight: bold;
  font-size: 16px;
  text-transform: capitalize;
}
.btn-orange {
  background: #e99140 !important;
  border-color: #c66b17 !important;
}
.btn-red {
  background: #cf4949 !important;
  border-color: #a12a2a !important;
}
.btn {
  height: 50px;
  min-height: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 10px;
  color: #f8f8f8;
  cursor: pointer;
  font-weight: 500;
  border-radius: 4px;
  font-family: inherit;
  transition: opacity 0.3s, color 0.3s, background-color 0.3s, border-color 0.3s,
    height 0.3s;
  flex-shrink: 0;
  position: relative;
  overflow: hidden;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  padding: 10px;
  margin-top: 10px;
}
.pauseTime {
  font-size: 14px;
  text-align: center !important;
}
#workingTime {
  font-size: 40px;
  font-weight: bold;
}
</style>