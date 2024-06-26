@inject('coreConfigRepository', 'Webkul\Core\Repositories\CoreConfigRepository')

@php
    $nameKey = $item['key'] . '.' . $field['name'];

    $name = $coreConfigRepository->getNameField($nameKey);

    $value = $coreConfigRepository->getValueByRepository($field);

    $validations = $coreConfigRepository->getValidations($field);

    $isRequired = Str::contains($validations, 'required') ? 'required' : '';

    $channelLocaleInfo = $coreConfigRepository->getChannelLocaleInfo($field, $currentChannel->code, $currentLocale->code);
@endphp

<input
    type="hidden"
    name="keys[]"
    value="{{ json_encode($item) }}"
/>

<x-admin::form.control-group>
    @if (! empty($field['depends']))
        @include('admin::configuration.dependent-field-type')
    @else
        <!-- Title of the input field -->
        <div class="flex justify-between">
            <x-admin::form.control-group.label
                :for="$name"
            >
                {!! __($field['title']) . ( __($field['title']) ? '<span class="'.$isRequired.'"></span>' : '') !!}

                @if (
                    ! empty($field['channel_based'])
                    && $channels->count() > 1
                )
                    <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600">
                        {{ $currentChannel->name }}
                    </span>
                @endif

                @if (! empty($field['locale_based']))
                    <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600">
                        {{ $currentLocale->name }}
                    </span>
                @endif
            </x-admin::form.control-group.label>
        </div>

        <!-- Text input -->
        @if ($field['type'] == 'text')
            <x-admin::form.control-group.control
                type="text"
                :id="$name"
                :name="$name"
                :value="old($nameKey) ?? (core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code) ? core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code) : ($field['default_value'] ?? ''))"
                :rules="$validations"
                :label="trans($field['title'])"
            />

        <!-- Password input -->
        @elseif ($field['type'] == 'password')
            <x-admin::form.control-group.control
                type="password"
                :id="$name"
                :name="$name"
                :rules="$validations"
                :value="old($nameKey) ?? core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code)"
                :label="trans($field['title'])"
            />

        <!-- Number input -->
        @elseif ($field['type'] == 'number')
            <x-admin::form.control-group.control
                type="number"
                :id="$name"
                :name="$name"
                :rules="$validations"
                :value="old($nameKey) ?? core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code)"
                :label="trans($field['title'])"
                :min="$field['name'] == 'minimum_order_amount'"
            />

        <!-- Color Input -->
        @elseif ($field['type'] == 'color')
            <x-admin::form.control-group.control
                type="color"
                :id="$name"
                :name="$name"
                :rules="$validations"
                :value="old($nameKey) ?? core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code)"
                :label="trans($field['title'])"
            />

        <!-- Textarea Input -->
        @elseif ($field['type'] == 'textarea')
            <x-admin::form.control-group.control
                type="textarea"
                class="text-gray-600 dark:text-gray-300"
                :id="$name"
                :name="$name"
                :rules="$validations"
                :value="old($nameKey) ?: core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code) ?: (isset($field['default_value']) ? $field['default_value'] : '')"
                :label="trans($field['title'])"
            />

        <!-- Textarea Input -->
        @elseif ($field['type'] == 'editor')
            <!-- (@suraj-webkul) TODO Change textarea to tiny mce -->
            <x-admin::form.control-group.control
                type="textarea"
                :id="$name"
                :name="$name"
                :rules="$validations"
                :value="old($nameKey) ?: core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code) ?: (isset($field['default_value']) ? $field['default_value'] : '')"
                :label="trans($field['title'])"
            />

        <!-- Select input -->
        @elseif ($field['type'] == 'select')
            @php $selectedOption = core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code) ?? ''; @endphp

            <x-admin::form.control-group.control
                type="select"
                :id="$name"
                :name="$name"
                :rules="$validations"
                :value="$selectedOption"
                :label="trans($field['title'])"
            >
                @if (isset($field['repository']))
                    @foreach ($value as $key => $option)
                        <option
                            value="{{ $key }}"
                            {{ $key == $selectedOption ? 'selected' : ''}}
                        >
                            @lang($option)
                        </option>
                    @endforeach
                @else
                    @foreach ($field['options'] as $option)
                        <option
                            value="{{ $option['value'] ?? 0 }}"
                            {{ $value == $selectedOption ? 'selected' : ''}}
                        >
                            @lang($option['title'])
                        </option>
                    @endforeach
                @endif
            </x-admin::form.control-group.control>

        <!-- Multiselect Input -->
        @elseif ($field['type'] == 'multiselect')
            @php $selectedOption = core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code) ?? ''; @endphp

            <v-field
                name="{{ $name }}[]"
                id="{{ $name }}"
                rules="{{ $validations }}"
                label="{{ trans($field['title']) }}"
                multiple
            >
                <select
                    name="{{ $name }}[]"
                    class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                    :class="[errors['{{ $name }}[]'] ? 'border border-red-600 hover:border-red-600' : '']"
                    multiple
                >
                    @if (isset($field['repository']))
                        @foreach ($value as $key => $option)
                            <option 
                                value="{{ $key }}"
                                {{ in_array($key, explode(',', $selectedOption)) ? 'selected' : ''}}
                            >
                                {{ trans($value[$key]) }}
                            </option>
                        @endforeach
                    @else
                        @foreach ($field['options'] as $option)
                            <option 
                                value="{{ $value = $option['value'] ?? 0 }}"
                                {{ in_array($value, explode(',', $selectedOption)) ? 'selected' : ''}}
                            >
                                @lang($option['title'])
                            </option>
                         @endforeach
                    @endif
                </select>
            </v-field>


        <!-- Boolean/Switch input -->
        @elseif ($field['type'] == 'boolean')
            @php
                $selectedOption = core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code) ?? ($field['default_value'] ?? '');
            @endphp

            <input
                type="hidden"
                name="{{ $name }}"
                value="0"
            />

            <label class="relative inline-flex cursor-pointer items-center">
                <input  
                    type="checkbox"
                    name="{{ $name }}"
                    value="1"
                    id="{{ $name }}"
                    class="peer sr-only"
                    {{ $selectedOption ? 'checked' : '' }}
                >

                <div class="peer h-5 w-9 rounded-full bg-gray-200 after:absolute after:start-[2px] after:top-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:border-gray-600 dark:bg-gray-700 rtl:peer-checked:after:-translate-x-full"></div>
            </label>

        @elseif ($field['type'] == 'image')

            @php
                $src = Storage::url(core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code));
                $result = core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code);
            @endphp

            <div class="flex items-center justify-center">
                @if ($result)
                    <a
                        href="{{ $src }}"
                        target="_blank"
                    >
                        <img
                            src="{{ $src }}"
                            class="top-15 rounded-3 border-3 relative mr-5 h-[33px] w-[33px] border-gray-500"
                        />
                    </a>
                @endif

                <x-admin::form.control-group.control
                    type="file"
                    :id="$name"
                    :name="$name"
                    :rules="$validations"
                    :label="trans($field['title'])"
                />
            </div>

            @if ($result)
                <x-admin::form.control-group class="mt-1.5 flex w-max cursor-pointer select-none items-center gap-1.5">
                    <x-admin::form.control-group.control
                        type="checkbox"
                        class="peer"
                        :id="$name.'[delete]'"
                        :name="$name.'[delete]'"
                        value="1"
                        :for="$name.'[delete]'"
                    />

                    <label
                        for="{{ $name }}[delete]"
                        class="cursor-pointer !text-sm !font-semibold !text-gray-600 dark:!text-gray-300"
                    >
                        @lang('admin::app.configuration.index.delete')
                    </label>
                </x-admin::form.control-group>
            @endif

        @elseif ($field['type'] == 'file')
            @php
                $result = core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code);
                $src = explode("/", $result);
                $path = end($src);
            @endphp

            @if ($result)
                <a
                    href="{{ route('admin.configuration.download', [request()->route('slug'), request()->route('slug2'), $path]) }}"
                >
                    <i class="icon sort-down-icon download"></i>
                </a>
            @endif

            <x-admin::form.control-group.control
                type="file"
                :id="$name"
                :name="$name"
                :rules="$validations"
                :label="trans($field['title'])"
            />

            @if ($result)
                <div class="flex cursor-pointer gap-2.5">
                    <x-admin::form.control-group.control
                        type="checkbox"
                        class="peer"
                        :id="$name.'[delete]'"
                        :name="$name.'[delete]'"
                        value="1"
                    />

                    <label
                        class="cursor-pointer"
                        for="{{ $name }}[delete]'"
                    >
                        @lang('admin::app.configuration.index.delete')
                    </label>
                </div>
            @endif

        <!-- Country select Vue component -->
        @elseif ($field['type'] == 'country')
            <v-country ref="countryRef">
                <template v-slot:default="{ changeCountry }">
                    <x-admin::form.control-group class="flex">
                        <x-admin::form.control-group.control
                            type="select"
                            :id="$name"
                            :name="$name"
                            :rules="$validations"
                            :value="core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code)"
                            :label="trans($field['title'])"
                            @change="changeCountry($event.target.value)"
                        >
                            <option value="">
                                @lang('admin::app.configuration.index.select-country')
                            </option>

                            @foreach (core()->countries() as $country)
                                <option value="{{ $country->code }}">
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </x-admin::form.control-group.control>
                    </x-admin::form.control-group>
                </template>
            </v-country>

        <!-- State select Vue component -->
        @elseif ($field['type'] == 'state')
            <v-state ref="stateRef">
                <template
                    v-slot:default="{ countryStates, country, haveStates, isStateComponenetLoaded }"
                >
                    <div v-if="isStateComponenetLoaded">
                        <template v-if="haveStates()">
                            <x-admin::form.control-group class="flex">
                                <x-admin::form.control-group.control
                                    type="select"
                                    :id="$name"
                                    :name="$name"
                                    :rules="$validations"
                                    :value="core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code)"
                                    :label="trans($field['title'])"
                                >
                                    <option value="">
                                        @lang('admin::app.configuration.index.select-state')
                                    </option>
                                    
                                    <option
                                        v-for='(state, index) in countryStates[country]'
                                        :value="state.code"
                                    >
                                        @{{ state.default_name }}
                                    </option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>
                        </template>

                        <template v-else>
                            <x-admin::form.control-group class="flex">
                                <x-admin::form.control-group.control
                                    type="text"
                                    :id="$name"
                                    :name="$name"
                                    :rules="$validations"
                                    :value="core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code)"
                                    :label="trans($field['title'])"
                                />
                            </x-admin::form.control-group>
                        </template>
                    </div>
                </template>
            </v-state>
        @endif

        @if (isset($field['info']))
            <label
                class="block text-xs font-medium leading-5 text-gray-600 dark:text-gray-300"
                for="{{ $name }}-info"
            >
                {!! trans($field['info']) !!}
            </label>
        @endif

        <!-- Input field validaitons error message -->
        <x-admin::form.control-group.error
            :control-name="$name"
        >
        </x-admin::form.control-group.error>
    @endif
</x-admin::form.control-group>

@if ($field['type'] == 'country')
    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-country-template"
        >
            <div>
                <slot
                    :changeCountry="changeCountry"
                >
                </slot>
            </div>
        </script>

        <script type="module">
            app.component('v-country', {
                template: '#v-country-template',

                data() {
                    return {
                        country: "{{ core()->getConfigData($nameKey, $currentChannel->code, $currentLocale->code) ?? '' }}",
                    }
                },

                mounted() {
                    this.$root.$refs.stateRef.country = this.country;
                },

                methods: {
                    changeCountry(selectedCountryCode) {
                        this.$root.$refs.stateRef.country = selectedCountryCode;
                    },
                },
            });
        </script>

        <script
            type="text/x-template"
            id="v-state-template"
        >
            <div>
                <slot
                    :country="country"
                    :country-states="countryStates"
                    :have-states="haveStates"
                    :is-state-componenet-loaded="isStateComponenetLoaded"
                >
                </slot>
            </div>
        </script>

        <script type="module">
            app.component('v-state', {
                template: '#v-state-template',

                data() {
                    return {
                        country: "",

                        isStateComponenetLoaded: false,

                        countryStates: @json(core()->groupedStatesByCountries())
                    }
                },

                created() {
                    setTimeout(() => {
                        this.isStateComponenetLoaded = true;
                    }, 0);
                },

                methods: {
                    haveStates() {
                        /*
                         * The double negation operator is used to convert the value to a boolean.
                         * It ensures that the final result is a boolean value,
                         * true if the array has a length greater than 0, and otherwise false.
                         */
                        return !!this.countryStates[this.country]?.length;
                    },
                },
            });
        </script>
    @endPushOnce
@endif
