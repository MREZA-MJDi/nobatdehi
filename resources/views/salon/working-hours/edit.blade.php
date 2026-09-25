@extends('layouts.salon')

@section('title', 'ساعات کاری')

@section('content')
@php
    $days = [
        0 => ['name' => 'شنبه', 'short' => 'ش'],
        1 => ['name' => 'یکشنبه', 'short' => 'ی'],
        2 => ['name' => 'دوشنبه', 'short' => 'د'],
        3 => ['name' => 'سه‌شنبه', 'short' => 'س'],
        4 => ['name' => 'چهارشنبه', 'short' => 'چ'],
        5 => ['name' => 'پنجشنبه', 'short' => 'پ'],
        6 => ['name' => 'جمعه', 'short' => 'ج'],
    ];
@endphp

<script>
    function workingHoursPage() {
        return {
            schedules: @js($schedules),
            selectedScope: @js($selectedScope),
            formError: '',
            copyModalOpen: false,
            copySourceDay: null,
            copyTargets: { 0:false, 1:false, 2:false, 3:false, 4:false, 5:false, 6:false },

            get currentSchedule() {
                return this.schedules[this.selectedScope];
            },

            get currentHours() {
                return this.currentSchedule?.hours || {};
            },

            get scopeLabel() {
                return this.selectedScope === 'salon'
                    ? 'برنامه کلی سالن'
                    : 'برنامه اختصاصی ' + (this.currentSchedule?.name || 'آرایشگر');
            },

            defaultSchedule() {
                return {
                    0:{closed:false,intervals:[{start:'09:00',end:'22:00'}]},
                    1:{closed:false,intervals:[{start:'09:00',end:'22:00'}]},
                    2:{closed:false,intervals:[{start:'09:00',end:'22:00'}]},
                    3:{closed:false,intervals:[{start:'09:00',end:'22:00'}]},
                    4:{closed:false,intervals:[{start:'09:00',end:'22:00'}]},
                    5:{closed:false,intervals:[{start:'09:00',end:'22:00'}]},
                    6:{closed:true,intervals:[]},
                };
            },

            selectScope(scope) {
                this.selectedScope = String(scope);
                this.formError = '';
                this.copyModalOpen = false;
            },

            markCustomized() {
                if (this.selectedScope !== 'salon' && this.currentSchedule) {
                    this.currentSchedule.inherited = false;
                }
                this.formError = '';
            },

            applyDefault() {
                if (!confirm('برنامه پیشنهادی روی برنامه فعلی اعمال شود؟')) return;
                this.currentSchedule.hours = JSON.parse(JSON.stringify(this.defaultSchedule()));
                this.markCustomized();
            },

            addInterval(day) {
                const target = this.currentHours[day];
                target.closed = false;
                if (!Array.isArray(target.intervals)) target.intervals = [];
                target.intervals.push({start:'',end:''});
                this.markCustomized();
            },

            removeInterval(day,index) {
                this.currentHours[day].intervals.splice(index,1);
                if (!this.currentHours[day].intervals.length) {
                    this.currentHours[day].closed = true;
                }
                this.markCustomized();
            },

            closeDay(day) {
                this.currentHours[day].closed = true;
                this.currentHours[day].intervals = [];
                this.markCustomized();
            },

            openDay(day) {
                const target = this.currentHours[day];
                target.closed = false;
                if (!Array.isArray(target.intervals)) target.intervals = [];
                if (!target.intervals.length) {
                    target.intervals.push({start:'09:00',end:'22:00'});
                }
                this.markCustomized();
            },

            intervalError(day) {
                const target = this.currentHours[day];
                if (!target || target.closed) return '';

                const intervals = Array.isArray(target.intervals) ? target.intervals : [];
                if (!intervals.length) return 'حداقل یک بازه کاری لازم است.';

                const normalized = [];
                for (const interval of intervals) {
                    if (!interval.start || !interval.end) return 'ساعت شروع و پایان را کامل کنید.';
                    if (interval.start >= interval.end) return 'ساعت پایان باید بعد از شروع باشد.';
                    normalized.push({start:interval.start,end:interval.end});
                }

                normalized.sort((a,b) => a.start.localeCompare(b.start));

                for (let index=1; index<normalized.length; index++) {
                    if (normalized[index].start < normalized[index-1].end) {
                        return 'بازه‌ها نباید با هم تداخل داشته باشند.';
                    }
                }

                return '';
            },

            validate() {
                const days = ['شنبه','یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه'];

                for (const day of Object.keys(this.currentHours)) {
                    const error = this.intervalError(day);
                    if (error) {
                        this.formError = this.scopeLabel + ' · ' + days[Number(day)] + ': ' + error;
                        return false;
                    }
                }

                this.formError = '';
                return true;
            },

            handleSubmit(event) {
                if (!this.validate()) {
                    event.preventDefault();
                    window.scrollTo({top:0,behavior:'smooth'});
                } else {
                    event.currentTarget.querySelector('button[type="submit"]').disabled = true;
                }
            },

            openCopyModal(day) {
                this.copySourceDay = Number(day);
                Object.keys(this.copyTargets).forEach(dayNumber => this.copyTargets[dayNumber] = false);
                this.copyModalOpen = true;
            },

            closeCopyModal() {
                this.copyModalOpen = false;
                this.copySourceDay = null;
            },

            selectWorkingDays() {
                Object.keys(this.copyTargets).forEach(dayNumber => {
                    const number = Number(dayNumber);
                    this.copyTargets[dayNumber] = number !== this.copySourceDay && number <= 5;
                });
            },

            applyCopy() {
                if (this.copySourceDay === null) return;

                const targets = Object.entries(this.copyTargets)
                    .filter(([day,selected]) => selected && Number(day) !== this.copySourceDay)
                    .map(([day]) => Number(day));

                if (!targets.length) {
                    this.formError = 'حداقل یک روز مقصد را انتخاب کنید.';
                    this.copyModalOpen = false;
                    return;
                }

                const source = JSON.parse(JSON.stringify(this.currentHours[this.copySourceDay]));
                targets.forEach(day => this.currentHours[day] = JSON.parse(JSON.stringify(source)));

                this.markCustomized();
                this.closeCopyModal();
            },
        };
    }
</script>

<div x-data="workingHoursPage()" class="salon-working-hours-page">
    <header class="salon-page-header">
        <div>
            <span class="salon-overline">زمان‌بندی سالن</span>
            <h1>ساعات کاری</h1>
            <p>
                ابتدا مشخص کن برنامه برای کل سالن است یا برای کدام آرایشگر.
                آرایشگرانی که برنامه اختصاصی ندارند از برنامه کلی سالن استفاده می‌کنند.
            </p>
        </div>
    </header>

    @if(session('success'))
        <div class="salon-owner__flash is-success" role="status" aria-live="polite">
            <span aria-hidden="true">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="salon-owner__flash is-error" role="alert">
            <span aria-hidden="true">!</span>
            <div class="salon-owner__flash-copy">
                <strong>ساعات کاری ذخیره نشد</strong>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <section class="salon-working-hours-scope salon-card">
        <div class="salon-card-head">
            <div>
                <span class="salon-overline">مقصد برنامه</span>
                <h2>این ساعت کاری برای چه کسی است؟</h2>
            </div>
        </div>

        <div class="salon-working-hours-scope__body">
            <div class="salon-working-hours-scopes" role="tablist">
                <button type="button"
                    role="tab"
                    @click="selectScope('salon')"
                    :aria-selected="selectedScope === 'salon'"
                    class="salon-working-hours-scope-button"
                    :class="selectedScope === 'salon' ? 'is-active' : ''">
                    <span class="salon-working-hours-scope-button__icon">⌂</span>
                    <span>
                        <strong>کل سالن</strong>
                        <small>برنامه پایه</small>
                    </span>
                </button>

                @foreach($barbers as $barber)
                    <button type="button"
                        role="tab"
                        @click="selectScope('{{ $barber->id }}')"
                        :aria-selected="selectedScope === '{{ $barber->id }}'"
                        class="salon-working-hours-scope-button"
                        :class="selectedScope === '{{ $barber->id }}' ? 'is-active' : ''">
                        <span class="salon-working-hours-scope-button__avatar">
                            {{ mb_substr($barber->name,0,1) }}
                        </span>
                        <span>
                            <strong>{{ $barber->name }}</strong>
                            <small>{{ $barber->specialty ?: 'آرایشگر' }}</small>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <section class="salon-working-hours-context salon-card">
        <div class="salon-working-hours-context__main">
            <div>
                <span class="salon-overline">برنامه فعال</span>
                <h2 x-text="scopeLabel"></h2>

                <p x-show="selectedScope !== 'salon' && currentSchedule?.inherited" x-cloak>
                    این آرایشگر برنامه اختصاصی ندارد و فعلاً از برنامه کلی سالن استفاده می‌کند.
                    با ذخیره این فرم، برنامه اختصاصی او ثبت خواهد شد.
                </p>

                <p x-show="selectedScope !== 'salon' && !currentSchedule?.inherited" x-cloak>
                    این برنامه مستقل از برنامه کلی سالن است.
                </p>
            </div>

            <div class="salon-working-hours-actions">
                <button type="button" class="salon-btn salon-btn--quiet" @click="applyDefault()">
                    ⚡ برنامه پیشنهادی
                </button>
            </div>
        </div>
    </section>

    <template x-if="formError">
        <div class="salon-owner__flash is-error" role="alert" x-text="formError"></div>
    </template>

    <form method="POST"
        action="{{ route('salon.working-hours.update') }}"
        @submit="handleSubmit($event)"
        class="salon-working-hours-form">
        @csrf
        @method('PUT')

        <input type="hidden" name="barber_id" :value="selectedScope === 'salon' ? '' : selectedScope">

        <div class="salon-working-hours-days">
            @foreach($days as $dayNumber => $day)
                <section class="salon-working-day salon-card">
                    <input type="hidden" name="hours[{{ $dayNumber }}][day_of_week]" value="{{ $dayNumber }}">
                    <input type="hidden" name="hours[{{ $dayNumber }}][is_closed]" :value="currentHours[{{ $dayNumber }}].closed ? 1 : 0">

                    <header class="salon-working-day__head">
                        <div class="salon-working-day__identity">
                            <span class="salon-working-day__index">{{ $day['short'] }}</span>
                            <div>
                                <strong>{{ $day['name'] }}</strong>
                                <small
                                    :class="currentHours[{{ $dayNumber }}].closed ? 'is-closed' : 'is-open'"
                                    x-text="currentHours[{{ $dayNumber }}].closed ? 'تعطیل' : 'باز و آماده رزرو'">
                                </small>
                            </div>
                        </div>

                        <div class="salon-working-day__tools">
                            <button type="button"
                                class="salon-working-day__toggle"
                                @click="currentHours[{{ $dayNumber }}].closed ? openDay({{ $dayNumber }}) : closeDay({{ $dayNumber }})"
                                x-text="currentHours[{{ $dayNumber }}].closed ? 'باز کردن' : 'تعطیل کردن'">
                            </button>

                            <button type="button"
                                class="salon-working-day__copy"
                                @click="openCopyModal({{ $dayNumber }})">
                                کپی
                            </button>
                        </div>
                    </header>

                    <div x-show="!currentHours[{{ $dayNumber }}].closed" x-cloak class="salon-working-day__body">
                        <div class="salon-working-intervals">
                            <template x-for="(interval, intervalIndex) in currentHours[{{ $dayNumber }}].intervals" :key="intervalIndex">
                                <div class="salon-working-interval">
                                    <div class="salon-working-time-field">
                                        <label :for="'working-start-{{ $dayNumber }}-' + intervalIndex">شروع</label>
                                        <input
                                            type="time"
                                            step="900"
                                            :id="'working-start-{{ $dayNumber }}-' + intervalIndex"
                                            :name="'hours[{{ $dayNumber }}][intervals][' + intervalIndex + '][start_time]'"
                                            x-model="interval.start"
                                            @change="markCustomized()">
                                    </div>

                                    <span class="salon-working-interval-separator">تا</span>

                                    <div class="salon-working-time-field">
                                        <label :for="'working-end-{{ $dayNumber }}-' + intervalIndex">پایان</label>
                                        <input
                                            type="time"
                                            step="900"
                                            :id="'working-end-{{ $dayNumber }}-' + intervalIndex"
                                            :name="'hours[{{ $dayNumber }}][intervals][' + intervalIndex + '][end_time]'"
                                            x-model="interval.end"
                                            @change="markCustomized()">
                                    </div>

                                    <button type="button"
                                        class="salon-working-interval-remove"
                                        @click="removeInterval({{ $dayNumber }}, intervalIndex)">
                                        حذف
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="salon-working-day__footer">
                            <button type="button" class="salon-working-add" @click="addInterval({{ $dayNumber }})">
                                ＋ افزودن بازه
                            </button>

                            <span class="salon-working-day__error"
                                x-show="intervalError({{ $dayNumber }})"
                                x-cloak
                                x-text="intervalError({{ $dayNumber }})">
                            </span>
                        </div>
                    </div>

                    <div x-show="currentHours[{{ $dayNumber }}].closed" x-cloak class="salon-working-day__closed">
                        <div>
                            <strong>این روز برای این برنامه تعطیل است.</strong>
                            <span>مشتری در این روز زمان قابل رزرو نمی‌بیند.</span>
                        </div>

                        <button type="button" class="salon-btn salon-btn--quiet" @click="openDay({{ $dayNumber }})">
                            باز کردن روز
                        </button>
                    </div>
                </section>
            @endforeach
        </div>

        <div class="salon-working-hours-save">
            <div>
                <strong>برنامه انتخاب‌شده را ذخیره کن</strong>
                <span x-text="scopeLabel"></span>
            </div>

            <button type="submit" class="salon-btn salon-btn--primary salon-working-hours-save__button">
                ذخیره ساعات کاری
                <span aria-hidden="true">✓</span>
            </button>
        </div>
    </form>

    <div x-show="copyModalOpen" x-cloak x-transition.opacity class="salon-working-hours-modal">
        <div class="salon-working-hours-modal__dialog" @click.outside="closeCopyModal()">
            <header>
                <div>
                    <span class="salon-overline">کپی برنامه</span>
                    <h2>این بازه برای کدام روزها کپی شود؟</h2>
                </div>
                <button type="button" @click="closeCopyModal()" aria-label="بستن">×</button>
            </header>

            <div class="salon-working-hours-modal__source">
                <span>مبدا</span>
                <strong x-text="copySourceDay !== null ? ['شنبه','یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه'][copySourceDay] : ''"></strong>
            </div>

            <button type="button" class="salon-working-hours-modal__select-all" @click="selectWorkingDays()">
                انتخاب روزهای کاری
            </button>

            <div class="salon-working-hours-modal__days">
                @foreach($days as $dayNumber => $day)
                    <label x-show="copySourceDay !== {{ $dayNumber }}">
                        <span>{{ $day['name'] }}</span>
                        <input type="checkbox" x-model="copyTargets[{{ $dayNumber }}]">
                    </label>
                @endforeach
            </div>

            <footer>
                <button type="button" class="salon-btn" @click="closeCopyModal()">انصراف</button>
                <button type="button" class="salon-btn salon-btn--primary" @click="applyCopy()">اعمال کپی</button>
            </footer>
        </div>
    </div>
</div>
@endsection
