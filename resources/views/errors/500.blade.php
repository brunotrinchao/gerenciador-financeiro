@extends('errors::layout')

@section('title', __('errors.server_error_title'))
@section('code', '500')
@section('message', __('errors.server_error_message'))

