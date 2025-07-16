@extends('errors::layout')

@section('title', __('errors.service_unavailable_title'))
@section('code', '503')
@section('message', __('errors.service_unavailable_message'))
