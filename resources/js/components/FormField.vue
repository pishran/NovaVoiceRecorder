<template>
    <DefaultField
        :field="currentField"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="fullWidthContent"
    >
        <template #field>
            <div class="space-y-1">
                <Badge :label="`فایل صوتی به طول ${duration} ثانیه ضبط شد.`" v-if="audioData !== null"/>

                <DefaultButton size="sm" @click="startRecording" v-if="!isRecording && audioData === null">
                    <Icon class="mr-1" type="microphone" :solid="true"/>
                    شروع ضبط
                </DefaultButton>

                <DefaultButton size="sm" @click="stopRecording" v-if="isRecording">
                    <Icon class="mr-1" type="check" :solid="true"/>
                    پایان ضبط
                    <span class="ml-1">({{ duration }} ثانیه)</span>
                </DefaultButton>

                <input class="hidden" :name="field.attribute" :value="audioData"/>
            </div>
        </template>
    </DefaultField>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data() {
        return {
            isRecording: false,
            audioUrl: null,
            audioData: null,
            mediaRecorder: null,
            audioChunks: [],
            duration: 0,
            interval: null,
        };
    },

    methods: {
        async startRecording() {
            this.isRecording = true;
            const stream = await navigator.mediaDevices.getUserMedia({audio: true});
            this.mediaRecorder = new MediaRecorder(stream);
            this.mediaRecorder.ondataavailable = e => this.audioChunks.push(e.data);
            this.mediaRecorder.onstop = this.handleStop;
            this.mediaRecorder.start();
            this.duration = 0;
            this.interval = setInterval(() => this.duration++, 1000)
        },

        stopRecording() {
            this.isRecording = false;
            this.mediaRecorder.stop();
            clearInterval(this.interval);
            this.interval = null;
        },

        handleStop() {
            const audioBlob = new Blob(this.audioChunks, {type: 'audio/mp3'});
            this.audioUrl = URL.createObjectURL(audioBlob);
            const reader = new FileReader();
            reader.readAsDataURL(audioBlob);
            reader.onloadend = () => this.audioData = reader.result;
            this.audioChunks = [];
        },

        fill(formData) {
            formData.append(this.fieldAttribute, this.audioData || '')
        },
    }
};
</script>
