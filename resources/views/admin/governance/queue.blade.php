@extends('layouts.admin')
@section('title','Approval queue')
@section('content')
<div class="governance">
@include('admin.governance.styles')
<h1>Approval queue</h1><p>Review proposed additions before they appear in the live store.</p>
<form method="GET" class="gov-toolbar gov-card">
<label for="status">Status</label><select id="status" name="status">@foreach(['pending','approved','rejected'] as $status)<option @selected(request('status','pending')===$status)>{{ $status }}</option>@endforeach</select>
<label for="per_page">Per page</label><select name="per_page" id="per_page">@foreach([25,50,100] as $size)<option @selected((int)request('per_page',25)===$size)>{{ $size }}</option>@endforeach</select><button class="gov-button secondary">Apply</button><span>{{ $requests->total() }} requests</span>
</form>
<form method="POST" action="{{ route('governance.review') }}" id="review-form" class="gov-card">@csrf
@if(request('status','pending')==='pending')
<div class="gov-toolbar"><label><input type="checkbox" id="select-page"> Select All on Current Page</label><span id="selected-count" aria-live="polite">0 selected</span><button class="gov-button" name="decision" value="approved">Approve Selected</button><button class="gov-button secondary" name="decision" value="rejected">Reject Selected</button></div>
<div class="gov-field"><label for="reason">Rejection reason (required when rejecting)</label><textarea name="reason" id="reason" rows="2" maxlength="2000" placeholder="Explain what needs to change."></textarea></div>
@endif
<div class="gov-scroll"><table class="gov-table"><thead><tr><th>Select</th><th>Request</th><th>Requesting admin</th><th>Submitted</th><th>Proposed data</th><th>Review</th></tr></thead><tbody>
@forelse($requests as $item)
<tr><td>@if($item->status==='pending')<input type="checkbox" name="ids[]" value="{{ $item->id }}" aria-label="Select request {{ $item->id }}">@endif</td><td><strong>#{{ $item->id }}</strong><br>{{ str_replace('_',' ',$item->entity_type) }}<br><span class="gov-tag">{{ $item->status }}</span></td><td>{{ $item->requester->full_name }}<br><small>{{ $item->requester->email }}</small></td><td>{{ $item->created_at->format('M j, Y H:i') }}</td>
<td><details class="gov-details"><summary>View proposed details</summary><dl>@foreach($item->payload as $key=>$value)@if($key!=='_images')<dt>{{ str_replace('_',' ',$key) }}</dt><dd>{{ is_scalar($value) ? $value : json_encode($value) }}</dd>@endif @endforeach</dl>
@foreach($item->payload['_images'] ?? [] as $path)<a href="{{ Storage::disk('supabase')->url($path) }}" target="_blank" rel="noopener"><img src="{{ Storage::disk('supabase')->url($path) }}" alt="Proposed product image" loading="lazy" style="width:90px;height:90px;object-fit:contain"></a>@endforeach</details></td>
<td>@if($item->status==='pending')<button type="button" class="gov-button" data-review="approved" data-id="{{ $item->id }}">Approve</button> <button type="button" class="gov-button secondary" data-review="rejected" data-id="{{ $item->id }}">Reject</button>@else{{ $item->reviewer?->full_name }}<br>{{ $item->reviewed_at?->format('M j, Y H:i') }}<p>{{ $item->rejection_reason }}</p>@endif</td></tr>
@empty<tr><td colspan="6">No requests in this queue.</td></tr>@endforelse
</tbody></table></div>
</form>{{ $requests->links() }}
<p>Review up to 100 requests at a time. Selection applies only to the current page.</p>
</div>
<script>
(()=>{const form=document.getElementById('review-form'), boxes=[...form.querySelectorAll('[name="ids[]"]')], all=document.getElementById('select-page'), count=document.getElementById('selected-count');
const update=()=>{if(count)count.textContent=boxes.filter(b=>b.checked).length+' selected';if(all){all.checked=boxes.length>0&&boxes.every(b=>b.checked);all.indeterminate=boxes.some(b=>b.checked)&&!all.checked;}};
all?.addEventListener('change',()=>{boxes.forEach(b=>b.checked=all.checked);update();});boxes.forEach(b=>b.addEventListener('change',update));
form.querySelectorAll('[data-review]').forEach(b=>b.addEventListener('click',()=>{boxes.forEach(c=>c.checked=c.value===b.dataset.id);update();document.getElementById('reason').required=b.dataset.review==='rejected';form.requestSubmit(form.querySelector('[name="decision"][value="'+b.dataset.review+'"]'));}));
form.querySelectorAll('[name="decision"]').forEach(button=>button.addEventListener('click',()=>{document.getElementById('reason').required=button.value==='rejected';}));
form.addEventListener('submit',e=>{const reason=document.getElementById('reason');reason.required=e.submitter?.value==='rejected';if(!boxes.some(b=>b.checked)){e.preventDefault();alert('Select at least one request.');return;}if(!form.reportValidity()){e.preventDefault();reason.required=false;}});})();
</script>
@endsection
