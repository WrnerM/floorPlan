USE [FloorPlan]
GO

/****** Object:  StoredProcedure [dbo].[usp_site_summary]    Script Date: 21/05/2025 12:19:14 am ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO




 
 


--exec [usp_site_summary] @dateValid = '2021-06-11 00:00:00.00000'

CREATE OR ALTER                             procedure [dbo].[usp_site_summary](
	@dateValid as nvarchar(50) = null
)

as begin 

	set nocount on
 
	declare @tbl as table (
		tags varchar(50)
	)
 
	declare @query nvarchar(max); 
	declare @pvt_columns nvarchar(max);
	declare @cols_def nvarchar(max);
 
	insert into @tbl

	select distinct tag from positions 

	where tag is not null and ltrim(rtrim(tag)) <> ''
 
	select @pvt_columns = STRING_AGG(QUOTENAME(tags), ', '),

			@cols_def = STRING_AGG(QUOTENAME(tags), ' + ')

	from (select tags FROM @tbl) as z;


	IF @dateValid IS NOT NULL AND LTRIM(RTRIM(@dateValid)) <> ''
		BEGIN
			set @query = N'
				select siteid, '+@pvt_columns+','+@cols_def+' as total_seats
				from (
					select floorid, siteid, tag from positions a
						inner join floors b on a.floorid = b.id
					where scenario is null AND dateValid = '''+@dateValid+'''
				) as src
				pivot (
					COUNT(floorid) for tag in ('+@pvt_columns+')
				) as pvt_table;';
		END
	ELSE 
		BEGIN 
			set @query = N'
				select siteid, '+@pvt_columns+','+@cols_def+' as total_seats
				from (
					select floorid, siteid, tag from positions_active a
						inner join floors b on a.floorid = b.id
						where scenario is null 
						AND dateValid <= getutcdate() at time zone ''UTC'' at time zone ''Singapore Standard Time''
				) as src
				pivot (
					COUNT(floorid) for tag in ('+@pvt_columns+')
				) as pvt_table;';
		END;
 
	execute(@query);
 
	end

 

GO


