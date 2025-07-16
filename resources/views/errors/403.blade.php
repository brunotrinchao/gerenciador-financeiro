@extends('errors::layout')

@section('title', __('errors.forbidden_title'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: __('errors.forbidden_message')))

